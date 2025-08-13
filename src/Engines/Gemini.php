<?php

declare(strict_types=1);

namespace Potato\AiBridge\Engines;

use InvalidArgumentException;
use Potato\AiBridge\Prompt;
use Potato\AiBridge\Request;
use Potato\AiBridge\Engine;
use Potato\AiBridge\Result;
use Psr\Http\Message\ResponseInterface;

class Gemini implements Engine
{
    public function __construct(
        private readonly string $apiKey
    ) {

    }

    public function request(Prompt $prompt): Request
    {
        return new Request(
            "https://generativelanguage.googleapis.com/v1beta/models/{$prompt->model}:generateContent?key={$this->apiKey}",
            [],
            [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $prompt->message,
                            ],
                        ],
                    ],
                ],
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
            isset($content['candidates'])
            && is_array($content['candidates'])
            && isset($content['candidates'][0])
            && is_array($content['candidates'][0])
            && isset($content['candidates'][0]['content'])
            && is_array($content['candidates'][0]['content'])
            && isset($content['candidates'][0]['content']['parts'])
            && is_array($content['candidates'][0]['content']['parts'])
            && isset($content['candidates'][0]['content']['parts'][0])
            && is_array($content['candidates'][0]['content']['parts'][0])
            && isset($content['candidates'][0]['content']['parts'][0]['text'])
            && is_string($content['candidates'][0]['content']['parts'][0]['text'])
        ) {
            $text = $content['candidates'][0]['content']['parts'][0]['text'];
        }

        if (!is_string($text)) {
            throw new InvalidArgumentException('Invalid response');
        }

        return new Result($text);
    }
}
