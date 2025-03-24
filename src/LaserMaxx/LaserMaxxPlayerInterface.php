<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\LaserMaxx;

use Lsr\Lg\Results\Interface\Models\PlayerInterface;

interface LaserMaxxPlayerInterface extends PlayerInterface
{

    public int $shotPoints {
        get;
        set;
    }
    public int $scoreAccuracy {
        get;
        set;
    }
    public int $scoreBonus {
        get;
        set;
    }
    public int $scorePowers {
        get;
        set;
    }
    public int $scoreMines {
        get;
        set;
    }
    public int $ammoRest {
        get;
        set;
    }
    public int $minesHits {
        get;
        set;
    }

    public int $hitsOther {
        get;
        set;
    }
    public int $hitsOwn {
        get;
        set;
    }
    public int $deathsOwn {
        get;
        set;
    }
    public int $deathsOther {
        get;
        set;
    }

    public bool $vip {
        get;
        set;
    }

    public string $myLasermaxx {
        get;
        set;
    }

    public int $scoreVip {
        get;
        set;
    }

    public function getBonusCount() : int;

    public function getRemainingLives() : int;

    public function getMines() : int;

}