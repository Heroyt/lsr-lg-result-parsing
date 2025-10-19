<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\LaserMaxx\Evo6;

use Lsr\LaserLiga\PlayerInterface;
use Lsr\Lg\Results\LaserMaxx\LaserMaxxPlayerInterface;

/**
 * @template G of Evo6GameInterface
 * @template T of Evo6TeamInterface
 * @template U of PlayerInterface
 * @extends LaserMaxxPlayerInterface<G, T, U>
 */
interface Evo6PlayerInterface extends LaserMaxxPlayerInterface
{

    public int $bonuses {
        get;
        set;
    }
    public int $activity {
        get;
        set;
    }
    public int $calories {
        get;
        set;
    }

    public int $scoreActivity {
        get;
        set;
    }

    public int $scoreEncouragement {
        get;
        set;
    }

    public int $scoreKnockout {
        get;
        set;
    }

    public int $scorePenalty {
        get;
        set;
    }

    public int $scoreReality {
        get;
        set;
    }

    public int $penaltyCount {
        get;
        set;
    }

    public bool $birthday {
        get;
        set;
    }

    public int $respawns {
        get;
    }

}