<?php

declare(strict_types=1);

namespace Tests\Unit;

use Potato\AiBridge\Client;
use Potato\AiBridge\Request;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Client as GuzzleHttpClient;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ClientTest extends TestCase
{
    public function testRequestReturnsResponseOnSuccess(): void
    {
        $mockGuzzle = $this->createMock(GuzzleHttpClient::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        $mockGuzzle->expects($this->once())
            ->method('post')
            ->willReturn($mockResponse);

        $mockResponse->method('getStatusCode')->willReturn(200);

        $request = new Request(
            'https://example.com/api',
            ['Authorization' => 'Bearer token'],
            ['foo' => 'bar']
        );

        $client = new Client($mockGuzzle);
        $response = $client->request($request);

        $this->assertSame($mockResponse, $response);
    }

    public function testRequestThrowsExceptionOnNon200Status(): void
    {
        $mockGuzzle = $this->createMock(GuzzleHttpClient::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        $mockGuzzle->expects($this->once())
            ->method('post')
            ->willReturn($mockResponse);

        $mockResponse->method('getStatusCode')->willReturn(500);

        $request = new Request(
            'https://example.com/api',
            ['Authorization' => 'Bearer token'],
            ['foo' => 'bar']
        );

        $client = new Client($mockGuzzle);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid response from Agent API');
        $client->request($request);
    }
}
