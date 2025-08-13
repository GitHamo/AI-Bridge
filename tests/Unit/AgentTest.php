<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Potato\AiBridge\Agent;
use Potato\AiBridge\Engine;
use Potato\AiBridge\Client;
use Potato\AiBridge\Engines\Gemini;
use Potato\AiBridge\Engines\GPT;
use Potato\AiBridge\Prompt;
use Potato\AiBridge\Request;
use Potato\AiBridge\Result;
use Psr\Http\Message\ResponseInterface;

class AgentTest extends TestCase
{
    private Engine&MockObject $engine;
    private Client&MockObject $client;
    private Agent $agent;

    protected function setUp(): void
    {
        $this->engine = $this->createMock(Engine::class);
        $this->client = $this->createMock(Client::class);
        $this->agent = new Agent($this->engine, $this->client);
    }

    public function testPromptReturnsExpectedString(): void
    {
        $model = "test-model";
        $instructions = ['Do this', 'Then that'];

        $promptMessage = "Do this.\nThen that.\n";

        $request = $this->createMock(Request::class);
        $response = $this->createMock(ResponseInterface::class);
        $result = $this->createConfiguredMock(Result::class, [
            '__toString' => $expected = "final result",
        ]);

        $this->engine->expects($this->once())
            ->method('request')
            ->with($this->callback(function ($arg) use ($promptMessage, $model) {
                return $arg instanceof Prompt
                    && $arg->message === $promptMessage
                    && $arg->model === $model;
            }))
            ->willReturn($request);

        $this->client->expects($this->once())
            ->method('request')
            ->with($request)
            ->willReturn($response);

        $this->engine->expects($this->once())
            ->method('response')
            ->with($response)
            ->willReturn($result);

        $actual = $this->agent->prompt($model, $instructions);

        $this->assertSame($expected, $actual);
    }

    public function testPromptWithoutInstructions(): void
    {
        $model = "simple-model";

        $this->engine->expects($this->never())
            ->method('request');
        $this->client->expects($this->never())
            ->method('request');
        $this->engine->expects($this->never())
                ->method('response');

        $actual = $this->agent->prompt($model, []);

        $this->assertSame('', $actual);
    }

    #[DataProvider('validEngineDataProvider')]
    public function testCreateReturnsAgentWithCorrectEngine(string $inputName, string $expectedEngineClass): void
    {
        $agent = Agent::create($inputName, 'api-key');

        $this->assertInstanceOf(Agent::class, $agent);

        // Reflection is used here only for test verification purposes.
        // This is safe in the test context and not used in production code.
        $reflection = new \ReflectionClass($agent);
        $engineProp = $reflection->getProperty('engine');
        $engineProp->setAccessible(true); // NOSONAR

        $engine = $engineProp->getValue($agent); // NOSONAR

        $this->assertInstanceOf($expectedEngineClass, $engine);
    }

    public static function validEngineDataProvider(): array
    {
        return [
            'gpt canonical' => ['gpt', GPT::class],
            'gpt uppercase' => ['GPT', GPT::class],
            'gpt with spaces' => ['  gpt  ', GPT::class],
            'gemini canonical' => ['gemini', Gemini::class],
            'gemini uppercase' => ['GEMINI', Gemini::class],
            'gemini with spaces' => ['  gemini  ', Gemini::class],
        ];
    }

    #[DataProvider('invalidEngineDataProvider')]
    public function testCreateThrowsExceptionForInvalidEngine(string $invalidName): void
    {
        $this->expectException(\ValueError::class); // Engines::from() throws ValueError for invalid enum
        Agent::create($invalidName, 'api-key');
    }

    public static function invalidEngineDataProvider(): array
    {
        return [
            [''],
            ['unknown'],
            ['gptx'],
            ['geminii'],
            ['123'],
        ];
    }
}
