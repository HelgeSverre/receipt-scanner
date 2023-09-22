<?php

namespace HelgeSverre\ReceiptParser\Services\Textract;

use Aws\Textract\TextractClient;
use Exception;
use HelgeSverre\ReceiptParser\Services\Textract\Data\S3Object;
use HelgeSverre\ReceiptParser\Services\Textract\Data\TextractResponse;
use Illuminate\Support\Arr;

use function array_filter;
use function range;
use function sleep;

class TextractService
{
    public function __construct(protected TextractClient $textractClient)
    {
    }

    public function s3ObjectToText(S3Object $s3Object, int $timeoutInSeconds = 60): ?string
    {
        $result = $this->textractClient->startDocumentTextDetection([
            'ClientRequestToken' => $s3Object->getClientRequestToken(),
            'DocumentLocation' => [
                'S3Object' => array_filter([
                    'Bucket' => $s3Object->bucket,
                    'Name' => $s3Object->name,
                    'Version' => $s3Object->version,
                ]),
            ],
        ]);

        $jobId = Arr::get($result, 'JobId');

        if (! $jobId) {
            return null;
        }

        // TODO: Do delta-time stuff for accurate timeout etc, quick-n-dirty sleep 1 second for now though
        foreach (range(0, $timeoutInSeconds) as $attempt) {
            if ($attempt > 0) {
                sleep(1);
            }

            $response = $this->textractClient->getDocumentTextDetection([
                'JobId' => $jobId,
            ]);

            $status = Arr::get($response, 'JobStatus');

            if ($status === 'IN_PROGRESS') {
                continue;
            }

            if ($status === 'PARTIAL_SUCCESS' || $status === 'FAILED') {
                throw new Exception('Failed');
            }

            if ($status === 'SUCCEEDED') {
                return TextractResponse::fromAwsResult($response)?->getText();
            }

            throw new Exception("Unhandled status '$status'");
        }

        throw new Exception('Timed out');
    }

    public function bytesToText(string $content): ?string
    {
        $result = $this->textractClient->detectDocumentText([
            'Document' => [
                'Bytes' => $content,
            ],
        ]);

        return TextractResponse::fromAwsResult($result)?->getText();
    }
}
