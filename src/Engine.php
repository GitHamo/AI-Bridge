<?php

declare(strict_types=1);

namespace Potato\AiBridge;

use Potato\AiBridge\Request;
use Psr\Http\Message\ResponseInterface;

interface Engine
{
    public function request(Prompt $prompt): Request;

    public function response(ResponseInterface $response): Result;
}
