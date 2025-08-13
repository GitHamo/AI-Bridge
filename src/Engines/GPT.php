<?php

declare(strict_types=1);

namespace Potato\AiBridge\Engines;

use InvalidArgumentException;
use Potato\AiBridge\Engine;
use Potato\AiBridge\Prompt;
use Potato\AiBridge\Request;
use Potato\AiBridge\Result;
use Psr\Http\Message\ResponseInterface;

class GPT implements Engine
{
    public function __construct(
        private readonly string $apiKey
    ) {

    }

    public function request(Prompt $prompt): Request
    {
        return new Request(
            'https://api.openai.com/v1/responses',
            [
                'Authorization' => "Bearer {$this->apiKey}",
            ],
            [
                'model' => $prompt->model,
                "store" => false,
                "input" => $prompt->message,
            ],
        );
    }

    public function response(ResponseInterface $response): Result
    {
        /**
         * @var array<string, mixed>
         */
        $content = json_decode($response->getBody()->getContents(), true);
        
        $text = null;
        if (
            isset($content['output'])
            && is_array($content['output'])
            && isset($content['output'][0])
            && is_array($content['output'][0])
            && isset($content['output'][0]['content'])
            && is_array($content['output'][0]['content'])
            && isset($content['output'][0]['content'][0])
            && is_array($content['output'][0]['content'][0])
            && isset($content['output'][0]['content'][0]['text'])
            && is_string($content['output'][0]['content'][0]['text'])
        ) {
            $text = $content['output'][0]['content'][0]['text'];
        }

        if (!is_string($text)) {
            throw new InvalidArgumentException('Invalid response');
        }

        return new Result($text);
    }
}
