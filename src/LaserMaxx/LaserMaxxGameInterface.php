<?php

declare(strict_types=1);

namespace Lsr\Lg\Results\LaserMaxx;

use Lsr\Lg\Results\Interface\Models\GameInterface;

interface LaserMaxxGameInterface extends GameInterface
{
    public int $fileNumber {
        get;
        set;
    }
    public int $lives {
        get;
        set;
    }
    public int $ammo {
        get;
        set;
    }
    public int $respawn {
        get;
        set;
    }
    public int $reloadClips {
        get;
        set;
    }

    public bool $allowFriendlyFire {
        get;
        set;
    }

    public bool $antiStalking {
        get;
        set;
    }

}
