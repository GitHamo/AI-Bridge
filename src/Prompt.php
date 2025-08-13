<?php

declare(strict_types=1);

namespace Potato\AiBridge;

class Prompt
{
    public function __construct(
        public readonly string $message,
        public readonly string $model,
    ) {
    }
}
