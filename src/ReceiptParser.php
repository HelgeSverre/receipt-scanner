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
    public function scan(TextContent|string $text, Model $model = Model::TURBO_INSTRUCT, int $maxTokens = 2000, float $temperature = 0.1): ?Receipt
    {

        $prompt = Prompt::load('receipt_v2')->replace('[REPLACE_TEXT]', $text)->toString();

        $params = [
            'model' => $model->value,
            'max_tokens' => $maxTokens,
            'temperature' => $temperature,
        ];

        $response = $model->isCompletion()
            ? OpenAI::completions()->create([...$params, 'prompt' => $prompt])
            : OpenAI::chat()->create([...$params, 'messages' => [['role' => 'user', 'content' => $prompt]]]);

        return $this->responseToDto($response);
    }

    protected function responseToDto(ChatResponse|CompletionResponse $response): ?Receipt
    {
        $text = match (true) {
            $response instanceof ChatResponse => $response->choices[0]->message->content,
            $response instanceof CompletionResponse => $response->choices[0]->text,
        };

        $json = json_decode($text, true);

        if ($json === null) {

            throw new InvalidJsonReturnedError("Invalid JSON returned: $text");
        }

        // TODO: Attempt to "fix" json by calling openai again, or using a package that handles broken json

        return Receipt::fromJson($json);

    }
}
