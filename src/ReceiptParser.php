<?php

namespace HelgeSverre\ReceiptParser;

use HelgeSverre\ReceiptParser\Data\Receipt;
use HelgeSverre\ReceiptParser\Enums\Model;
use HelgeSverre\ReceiptParser\Exceptions\InvalidJsonReturnedError;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse as ChatResponse;
use OpenAI\Responses\Completions\CreateResponse as CompletionResponse;

class ReceiptParser
{
    public function raw(
        array $data = [],
        Model $model = Model::TURBO_INSTRUCT,
        int $maxTokens = 2000,
        float $temperature = 0.1,
        string $template = 'receipt',
    ): array {
        $response = $this->sendRequest(
            prompt: Prompt::load($template, $data),
            params: [
                'model' => $model->value,
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
            ],
            model: $model
        );

        return $this->parseResponse($response);
    }

    public function scan(
        TextContent|string $text,
        Model $model = Model::TURBO_INSTRUCT,
        int $maxTokens = 2000,
        float $temperature = 0.1,
        string $template = 'receipt',
        bool $asArray = false,
    ): Receipt|array {
        $response = $this->sendRequest(
            prompt: Prompt::load($template, ['context' => $text]),
            params: [
                'model' => $model->value,
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
            ],
            model: $model
        );

        $data = $this->parseResponse($response);

        return $asArray ? $data : Receipt::fromJson($data);
    }

    protected function sendRequest(string $prompt, array $params, Model $model): ChatResponse|CompletionResponse
    {
        return $model->isCompletion()
            ? OpenAI::completions()->create(array_merge($params, ['prompt' => $prompt]))
            : OpenAI::chat()->create(array_merge($params, ['messages' => [['role' => 'user', 'content' => $prompt]]]));
    }

    protected function parseResponse(ChatResponse|CompletionResponse $response): array
    {
        $json = $this->extractResponseText($response);

        $decoded = json_decode($json, true);

        if ($decoded === null) {
            throw new InvalidJsonReturnedError("Invalid JSON returned:\n$json");
        }

        return $decoded;
    }

    protected function extractResponseText(ChatResponse|CompletionResponse $response): string
    {
        return $response instanceof ChatResponse
            ? $response->choices[0]->message->content
            : $response->choices[0]->text;
    }
}
