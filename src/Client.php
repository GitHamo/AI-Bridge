<?php

declare(strict_types=1);

namespace Potato\AiBridge;

use GuzzleHttp\Client as GuzzleHttpClient;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class Client
{
    public function __construct(private GuzzleHttpClient $client)
    {
    }

    public function request(Request $request): ResponseInterface
    {
        $response = $this->client->post($request->uri, [
            'headers' => [
                'Content-Type' => 'application/json',
            ] + $request->headers,
            'json' => $request->body,
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException('Invalid response from Agent API');
        }

        return $response;
    }
}
