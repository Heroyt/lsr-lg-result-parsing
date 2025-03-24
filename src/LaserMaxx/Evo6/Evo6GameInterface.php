<?php

declare(strict_types=1);

namespace Lsr\Lg\Results\LaserMaxx\Evo6;

use Lsr\Lg\Results\LaserMaxx\LaserMaxxGameInterface;

interface Evo6GameInterface extends LaserMaxxGameInterface
{
    public Scoring $scoring {
        get;
        set;
    }

    public TriggerSpeed $triggerSpeed {
        get;
        set;
    }

    public GameStyleType $gameStyleType {
        get;
        set;
    }

    public HitGainSettings $hitGainSettings {
        get;
        set;
    }

    public RespawnSettings $respawnSettings {
        get;
        set;
    }
}
