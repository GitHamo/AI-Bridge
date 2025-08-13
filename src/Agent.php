<?php

declare(strict_types=1);

namespace Potato\AiBridge;

use GuzzleHttp\Client as GuzzleHttpClient;
use Potato\AiBridge\Engines\Gemini;
use Potato\AiBridge\Engines\GPT;

final class Agent
{
    public function __construct(
        private Engine $engine,
        private Client $client,
    ) {
        //
    }

    /**
     * @param array<string, string> $instructions
     */
    public function prompt(string $model, array $instructions): string
    {
        if (empty($instructions)) {
            return '';
        }

        $promptMessage = implode("\n", array_map(fn ($i) => "{$i}.", $instructions)) . "\n";

        $request = $this->engine->request(new Prompt($promptMessage, $model));
        $response = $this->client->request($request);
        $result = $this->engine->response($response);

        return (string) $result;
    }

    public static function create(string $name, string $apiKey): self
    {
        $engineName = trim(strtolower($name));
        $engineEnum = Engines::from($engineName);

        $engine = match ($engineEnum) {
            Engines::GPT => new GPT($apiKey),
            Engines::GEMINI => new Gemini($apiKey),
        };

        $client = new Client(
            new GuzzleHttpClient(),
        );

        return new self($engine, $client);
    }
}
