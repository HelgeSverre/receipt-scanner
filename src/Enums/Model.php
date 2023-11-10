<?php

namespace HelgeSverre\ReceiptScanner\Enums;

namespace HelgeSverre\ReceiptScanner\Enums;

enum Model: string
{
    // Good, Faster
    case TURBO_INSTRUCT = 'gpt-3.5-turbo-instruct';

    case TURBO_1106 = 'gpt-3.5-turbo-1106';

    // Decent, fast-ish
    case TURBO_16K = 'gpt-3.5-turbo-16k';
    case TURBO = 'gpt-3.5-turbo';

    // Legacy Models
    case TURBO_0613 = 'gpt-3.5-turbo-0613';
    case TURBO_16K_0613 = 'gpt-3.5-turbo-16k-0613';
    case TURBO_0301 = 'gpt-3.5-turbo-0301';
    case TEXT_DAVINCI_003 = 'text-davinci-003';
    case TEXT_DAVINCI_002 = 'text-davinci-002';
    case CODE_DAVINCI_002 = 'code-davinci-002';

    // Smarter, slower
    case GPT4 = 'gpt-4';
    case GPT4_32K = 'gpt-4-32k';
    case GPT4_32K_0613 = 'gpt-4-32k-0613';
    case GPT4_1106_PREVIEW = 'gpt-4-1106-preview';

    // Legacy GPT-4 Models
    case GPT4_0314 = 'gpt-4-0314';
    case GPT4_32K_0314 = 'gpt-4-32k-0314';

    public function isCompletion(): bool
    {
        return match ($this) {
            self::TURBO_INSTRUCT => true,
            default => false,
        };
    }
}
