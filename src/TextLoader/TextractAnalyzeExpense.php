<?php

namespace HelgeSverre\ReceiptScanner\TextLoader;

use HelgeSverre\ReceiptScanner\Contracts\TextLoader;
use HelgeSverre\ReceiptScanner\Services\Textract\TextractService;
use HelgeSverre\ReceiptScanner\TextContent;

/**
 * Sends the input directly to the Textract AnalyzeExpense API, intended for images and small files.
 * Returns structured expense data (summary fields and line items) as text.
 * For PDFs or large files, use TextractAnalyzeExpenseUsingS3Upload instead.
 */
class TextractAnalyzeExpense implements TextLoader
{
    public function __construct(protected TextractService $textractService)
    {
    }

    public function load(mixed $data): ?TextContent
    {
        return new TextContent(
            $this->textractService->analyzeExpenseFromBytes($data)
        );
    }
}
