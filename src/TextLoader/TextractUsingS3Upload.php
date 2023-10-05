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

class TextractUsingS3Upload implements TextLoader
{
    public function __construct(protected TextractService $textractService)
    {
    }

    public function load(mixed $data): ?TextContent
    {
        $disk = config('receipt-scanner.textract_disk') ?: throw new Exception("Config 'receipt-scanner.textract_disk' is not set, it is required for OCR-ing PDFs");
        $bucket = config("filesystems.disks.$disk.bucket") ?: throw new Exception("Bucket is not defined in disk 'filesystems.disks.$disk.bucket'");
        $path = sprintf('receipt-scanner/%s.pdf', Str::uuid());

        $content = $data instanceof UploadedFile ? $data->getContent() : $data;

        $success = Storage::disk($disk)->put($path, $content) ?: throw new Exception('Could not store the file in the textract s3 bucket.');

        return new TextContent(
            $this->textractService->s3ObjectToText(
                s3Object: new S3Object(bucket: $bucket, name: $path),
                timeoutInSeconds: config('receipt-scanner.textract_timeout'),
                pollingIntervalInSeconds: config('receipt-scanner.textract_polling_interval')
            )
        );
    }
}
