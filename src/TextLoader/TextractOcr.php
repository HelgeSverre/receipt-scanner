<?php

namespace HelgeSverre\ReceiptParser\TextLoader;

use Exception;
use HelgeSverre\ReceiptParser\Contracts\TextLoader;
use HelgeSverre\ReceiptParser\Services\Textract\Data\S3Object;
use HelgeSverre\ReceiptParser\Services\Textract\TextractService;
use HelgeSverre\ReceiptParser\TextContent;
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

            if ($data->getMimeType() == 'application/pdf') {

                // todo: copy to s3 storage automatically and generate s3 object
                if (config('receipt-parser.textract_disk')) {
                    $path = sprintf('receipt-parser/%s.pdf', Str::uuid());

                    $disk = config('receipt-parser.textract_disk');

                    $bucket = config("filesystems.$disk.textract.bucket");

                    if (! $bucket) {
                        throw new Exception("Bucket is not defined in disk '$disk'");
                    }

                    $success = Storage::disk($disk)->put($path, $data->getContent());
                    if (! $success) {
                        throw new Exception('Could not upload file into the textract s3 bucket.');
                    }

                    $object = new S3Object(
                        bucket: $bucket,
                        name: $path,
                    );
                }
            }

            // TODO: throw if not known mimetype
            return new TextContent(
                $this->textractService->bytesToText($data->getContent())
            );
        }

        // Assuming it is the raw file contents
        return new TextContent(
            $this->textractService->bytesToText($data)
        );

        return null;
    }
}
