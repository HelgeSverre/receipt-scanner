<?php

namespace HelgeSverre\ReceiptScanner;

class ModelNames
{
    const DEFAULT = 'gpt-3.5-turbo-instruct';

    // Turbo Models
    const TURBO = 'gpt-3.5-turbo';
    const TURBO_INSTRUCT = 'gpt-3.5-turbo-instruct';
    const TURBO_1106 = 'gpt-3.5-turbo-1106';
    const TURBO_16K = 'gpt-3.5-turbo-16k';

    // Legacy Turbo Models
    const TURBO_0613 = 'gpt-3.5-turbo-0613';
    const TURBO_16K_0613 = 'gpt-3.5-turbo-16k-0613';
    const TURBO_0301 = 'gpt-3.5-turbo-0301';

    // GPT-4 Models
    const GPT4 = 'gpt-4';
    const GPT4_32K = 'gpt-4-32k';
    const GPT4_32K_0613 = 'gpt-4-32k-0613';
    const GPT4_1106_PREVIEW = 'gpt-4-1106-preview';

    // Legacy GPT-4 Models
    const GPT4_0314 = 'gpt-4-0314';
    const GPT4_32K_0314 = 'gpt-4-32k-0314';

    public static function isCompletionModel(string $modelName): bool
    {
        return match ($modelName) {
            self::TURBO_INSTRUCT => true,
            default => false,
        };
    }
}
