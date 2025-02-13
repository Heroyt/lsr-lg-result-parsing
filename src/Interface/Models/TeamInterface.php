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
        set;
    }

    public int $score {
        get;
        set;
    }

    public ?int $bonus {
        get;
        set;
    }

    public int $position {
        get;
        set;
    }

    public string $name {
        get;
        set;
    }

}