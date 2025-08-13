<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Potato\AiBridge\Result;

class ResultTest extends TestCase
{
    #[DataProvider('stringValueProvider')]
    public function testToStringReturnsValue(string $input, string $expected): void
    {
        $result = new Result($input);
        $this->assertSame($expected, (string)$result);
    }

    public static function stringValueProvider(): array
    {
        return [
            'normal string' => ['Hello, world!', 'Hello, world!'],
            'empty string' => ['', ''],
            'numeric string' => ['12345', '12345'],
            'special chars' => ["!@#\$%^&*()", "!@#\$%^&*()"],
            'unicode' => ['こんにちは', 'こんにちは'],
            'whitespace' => ["   \n\t", "   \n\t"],
        ];
    }
}
