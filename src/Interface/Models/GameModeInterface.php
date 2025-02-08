<?php

declare(strict_types=1);

namespace Lsr\Lg\Results\Interface\Models;

use Lsr\Lg\Results\Enums\GameModeType;

interface GameModeInterface
{
    public string $name {
        get;
        set;
    }
    public ?string $description {
        get;
        set;
    }
    public GameModeType $type {
        get;
        set;
    }
}
