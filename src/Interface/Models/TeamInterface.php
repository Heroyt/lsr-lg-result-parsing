<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\Interface\Models;

use JsonSerializable;
use Lsr\Lg\Results\Interface\WithGameInterface;
use Lsr\Lg\Results\Interface\WithPlayersInterface;

interface TeamInterface extends WithPlayersInterface, WithGameInterface, JsonSerializable, ModelInterface
{

    public int $color {
        get;
    }

    public int $score {
        get;
    }

    public ?int $bonus {
        get;
    }

    public int $position {
        get;
    }

    public string $name {
        get;
    }

}