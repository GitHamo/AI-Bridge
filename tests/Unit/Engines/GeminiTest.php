<?php

declare(strict_types=1);

namespace Tests\Unit\Agents;

use Potato\AiBridge\Prompt;
use Potato\AiBridge\Request;
use Potato\AiBridge\Result;
use Psr\Http\Message\ResponseInterface;
use PHPUnit\Framework\MockObject\MockObject;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Potato\AiBridge\Engines\Gemini;
use Psr\Http\Message\StreamInterface;

class GeminiTest extends TestCase
{
    public function testRequestReturnsCorrectRequest()
    {
        $apiKey = 'test-api-key';
        $model = 'gemini-pro';
        $message = 'Hello, Gemini!';
        $expected = new Request(
            $uri ="https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
            [],
           [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $message,
                            ],
                        ],
                    ],
                ],
            ],
        );

        $prompt = new Prompt($message, $model);
        $engine = new Gemini($apiKey);
        $actual = $engine->request($prompt);

        $this->assertInstanceOf(Request::class, $actual);
        $this->assertEquals($expected, $actual);

        $this->assertStringContainsString($uri, $actual->uri);
        $this->assertIsArray($actual->headers);
        $this->assertArrayHasKey('contents', $actual->body);
        $this->assertEquals($message, $actual->body['contents'][0]['parts'][0]['text']);
    }

    public function testResponseReturnsResultOnValidResponse()
    {
        $responseData = [
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            [
                                'text' => 'Gemini response text',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        /** @var ResponseInterface|MockObject $response */
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn(json_encode($responseData));
        $response->method('getBody')->willReturn($stream);

        $engine = new Gemini('dummy-key');
        $result = $engine->response($response);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals('Gemini response text', (string) $result);
    }

    public function testResponseThrowsExceptionOnInvalidResponse()
    {
        $invalidResponseData = ['unexpected' => 'structure'];

        /** @var ResponseInterface|MockObject $response */
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn(json_encode($invalidResponseData));
        $response->method('getBody')->willReturn($stream);

        $engine = new Gemini('dummy-key');

        $this->expectException(InvalidArgumentException::class);
        $engine->response($response);
    }
}
