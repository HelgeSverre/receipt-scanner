<?php

namespace HelgeSverre\ReceiptScanner;

use HelgeSverre\ReceiptScanner\Data\Receipt;
use HelgeSverre\ReceiptScanner\Exceptions\InvalidJsonReturnedError;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse as ChatResponse;
use OpenAI\Responses\Completions\CreateResponse as CompletionResponse;

class ReceiptScanner
{
    public function raw(
        array $data = [],
        string $model = ModelNames::DEFAULT,
        int $maxTokens = 2000,
        float $temperature = 0.1,
        string $template = 'receipt',
    ): array {
        $response = $this->sendRequest(
            prompt: Prompt::load($template, $data),
            params: [
                'model' => $model,
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
            ],
        );

        return $this->parseResponse($response);
    }

    /**
     * @throws InvalidJsonReturnedError
     */
    public function scan(
        TextContent|string $text,
        string $model = ModelNames::DEFAULT,
        int $maxTokens = 2000,
        float $temperature = 0.1,
        string $template = 'receipt',
        bool $asArray = false,
    ): Receipt|array {
        $response = $this->sendRequest(
            prompt: Prompt::load($template, ['context' => $text]),
            params: [
                'model' => $model,
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
                'response_format' => ['type' => 'json_object'],
            ],

            isCompletion: ModelNames::isCompletionModel($model)
        );

        $data = $this->parseResponse($response);

        return $asArray ? $data : Receipt::fromJson($data);
    }

    protected function sendRequest(string $prompt, array $params, bool $isCompletion = false): ChatResponse|CompletionResponse
    {
        return $isCompletion
            ? OpenAI::completions()->create(array_merge(Arr::except($params, ['response_format']), ['prompt' => $prompt]))
            : OpenAI::chat()->create(array_merge($params, ['messages' => [['role' => 'user', 'content' => $prompt]]]));
    }

    /**
     * @throws InvalidJsonReturnedError
     */
    protected function parseResponse(ChatResponse|CompletionResponse $response): array
    {
        $text = $this->extractResponseText($response);

        if ($data = json_decode($text, true)) {
            return $data;
        }

        if ($maybeData = json_decode(Str::between($text, '```json', '```'), true)) {
            return $maybeData;
        }

        throw new InvalidJsonReturnedError("Invalid JSON returned:\n$text");
    }

    protected function extractResponseText(ChatResponse|CompletionResponse $response): string
    {
        return $response instanceof ChatResponse
            ? $response->choices[0]->message->content
            : $response->choices[0]->text;
    }
}
