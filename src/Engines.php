<?php

declare(strict_types=1);

namespace Potato\AiBridge;

enum Engines: string
{
    case GPT = 'gpt';
    case GEMINI = 'gemini';
}
