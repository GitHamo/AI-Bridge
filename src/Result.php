<?php

declare(strict_types=1);

namespace Potato\AiBridge;

class Result
{
    public function __construct(
        private readonly string $value,
    ) {
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
