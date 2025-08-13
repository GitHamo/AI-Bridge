<?php

declare(strict_types=1);

namespace Tests\Unit\Agents;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Potato\AiBridge\Engines\GPT;
use Potato\AiBridge\Prompt;
use Potato\AiBridge\Request;
use Potato\AiBridge\Result;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class GPTTest extends TestCase
{
    private const string API_TOKEN = 'dummy-key';
    private const string BODY_INPUT = 'Hello, world!';
    private GPT $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agent = new GPT(
            self::API_TOKEN,
        );
    }

    public function testRequestReturnsCorrectRequest(): void
    {
        $expected = new Request(
            "https://api.openai.com/v1/responses",
            [
                'Authorization' => 'Bearer ' . self::API_TOKEN,
            ],
            [
                'model' => $model = 'foo-bar-baz',
                "store" => false,
                "input" => self::BODY_INPUT,
            ],
        );

        $prompt = new Prompt(self::BODY_INPUT, $model);
        $actual = $this->agent->request($prompt);

        $this->assertInstanceOf(Request::class, $actual);
        $this->assertEquals($expected, $actual);
    }

    public function testResponseReturnsResultOnValidStructure(): void
    {
        $responseBody = json_encode([
            'output' => [
                [
                    'content' => [
                        [
                            'text' => self::BODY_INPUT,
                        ],
                    ],
                ],
            ],
        ]);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn(
            $this->createConfiguredMock(
                StreamInterface::class,
                [
                    'getContents' => $responseBody,
                ]
            )
        );

        $actual = $this->agent->response($response);

        $this->assertInstanceOf(Result::class, $actual);
        $this->assertSame(self::BODY_INPUT, (string)$actual);
    }

    /**
     * @param <array<string, mixed>|bool|string> $structure
     */
    #[DataProvider('invalidResponseDataProvider')]
    public function testResponseThrowsOnInvalidStructure(array $structure): void
    {
        $responseBody = json_encode($structure);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn(
            $this->createConfiguredMock(
                StreamInterface::class,
                [
                    'getContents' => $responseBody,
                ]
            )
        );

        $this->expectException(InvalidArgumentException::class);

        $this->agent->response($response);
    }

    /**
     * @return array<string, <array<string, mixed>|bool|string>>
     */
    public static function invalidResponseDataProvider(): array
    {
        return [
            'missing_output' => [['output-not' => ['foo' => 'bar']]],
            'invalid_output' => [['output' => 'not-array']],
            'empty_output' => [['output' => []]],
            'missing_content' => [['output' => ['content-not' => ['foo' => 'bar']]]],
            'invalid_content' => [['output' => ['content' => 'not-array']]],
            'empty_content' => [['output' => ['content' => []]]],
            'missing_text' => [['output' => ['content' => ['text-not' => 'foobar']]]],
            'invalid_text' => [['output' => ['content' => ['text' => 123]]]],
        ];
    }
}
