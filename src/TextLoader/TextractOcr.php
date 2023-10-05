<?php

namespace HelgeSverre\ReceiptScanner\TextLoader;

use Exception;
use HelgeSverre\ReceiptScanner\Contracts\TextLoader;
use HelgeSverre\ReceiptScanner\Services\Textract\Data\S3Object;
use HelgeSverre\ReceiptScanner\Services\Textract\TextractService;
use HelgeSverre\ReceiptScanner\TextContent;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TextractOcr implements TextLoader
{
    public function __construct(protected TextractService $textractService)
    {
    }

    public function load(mixed $data): ?TextContent
    {
        if ($data instanceof UploadedFile) {
            return $this->loadFromUploadedFile($data);
        }

        return $this->loadFromRawData($data);
    }

    protected function loadFromUploadedFile(UploadedFile $file): ?TextContent
    {
        // Check file type and size
        $mimeType = $file->getMimeType();
        $size = $file->getSize();
        $mb = 1024 * 1024;

        if ($mimeType == 'application/pdf') {
            if ($size > 500 * $mb) {
                throw new Exception('PDF file size exceeds the 500MB limit.');
            }

            return $this->loadPdfFromS3($file);
        }

        if (in_array($mimeType, ['image/jpeg', 'image/png', 'image/bmp', 'image/gif', 'image/tiff'])) {
            if ($size > 5 * $mb) {
                throw new Exception('Image file size exceeds the 5MB limit.');
            }

            return $this->loadFromRawData($file->getContent());
        }

        throw new Exception("Unsupported mimetype: {$mimeType}");
    }

    protected function loadFromRawData(string $data): TextContent
    {
        return new TextContent(
            $this->textractService->bytesToText($data)
        );
    }

    protected function loadPdfFromS3(UploadedFile $file): TextContent
    {
        $disk = $this->getDisk();
        $bucket = $this->getBucket($disk);
        $path = $this->storeFile($disk, $file);

        return new TextContent(
            $this->textractService->s3ObjectToText(
                new S3Object(bucket: $bucket, name: $path)
            )
        );
    }

    protected function getDisk(): string
    {
        return config('receipt-scanner.textract_disk') ?: throw new Exception("Configuration option 'receipt-scanner.textract_disk' is not set, it is required for OCR-ing PDFs");
    }

    protected function getBucket(string $disk): string
    {
        return config("filesystems.disks.$disk.bucket") ?: throw new Exception("Bucket is not defined in disk '$disk'");
    }

    protected function storeFile(string $disk, UploadedFile $file): string
    {
        // TODO: Make path generation configurable, however this is sufficiently random to not cause collisions in any realistic scenario.
        $path = sprintf('receipt-scanner/%s.pdf', Str::uuid());
        $success = Storage::disk($disk)->put($path, $file->getContent());

        return $success ? $path : throw new Exception('Could not store the file in the textract s3 bucket.');
    }
}
