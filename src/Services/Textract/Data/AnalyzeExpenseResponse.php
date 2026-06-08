<?php

namespace HelgeSverre\ReceiptScanner\Services\Textract\Data;

use Aws\Result;
use Illuminate\Support\Arr;

class AnalyzeExpenseResponse
{
    protected ?string $text = null;

    protected array $raw = [];

    public static function fromAwsResult(Result $result): ?self
    {
        return self::fromArray($result->toArray());
    }

    public static function fromJson(string $json): ?self
    {
        return self::fromArray(json_decode($json, true));
    }

    public static function fromArray(array $array): ?self
    {
        $response = new self();
        $response->raw = $array;
        $response->text = $response->buildText($array);

        return $response;
    }

    protected function buildText(array $array): string
    {
        $lines = [];

        foreach (Arr::get($array, 'ExpenseDocuments', []) as $document) {
            $summaryFields = Arr::get($document, 'SummaryFields', []);

            if (! empty($summaryFields)) {
                $lines[] = 'SUMMARY FIELDS:';

                foreach ($summaryFields as $field) {
                    $type = Arr::get($field, 'Type.Text', Arr::get($field, 'LabelDetection.Text', ''));
                    $value = Arr::get($field, 'ValueDetection.Text', '');

                    if ($type !== '' && $value !== '') {
                        $lines[] = "$type: $value";
                    }
                }
            }

            $lineItemGroups = Arr::get($document, 'LineItemGroups', []);

            if (! empty($lineItemGroups)) {
                $lines[] = '';
                $lines[] = 'LINE ITEMS:';

                foreach ($lineItemGroups as $group) {
                    foreach (Arr::get($group, 'LineItems', []) as $lineItem) {
                        $fields = [];

                        foreach (Arr::get($lineItem, 'LineItemExpenseFields', []) as $expenseField) {
                            $type = Arr::get($expenseField, 'Type.Text', '');
                            $value = Arr::get($expenseField, 'ValueDetection.Text', '');

                            if ($type !== '' && $value !== '') {
                                $fields[] = "$type: $value";
                            }
                        }

                        if (! empty($fields)) {
                            $lines[] = '- '.implode(', ', $fields);
                        }
                    }
                }
            }
        }

        return implode("\n", $lines);
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function getRaw(): array
    {
        return $this->raw;
    }

    public function getRawJson(): string
    {
        return json_encode($this->raw);
    }
}
