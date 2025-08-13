<?php

declare(strict_types=1);

namespace Potato\AiBridge;

class Request
{
    /**
     * @param array<string, string> $headers
     * @param array<mixed> $body
     */
    public function __construct(
        public readonly string $uri,
        public readonly array $headers = [],
        public readonly array $body = [],
    ) {
    }
}
