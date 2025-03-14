<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\Interface\Models;

interface GroupTeamInterface
{
	public string $key {
		get;
	}

    public float $skill {
        get;
        set;
    }

    public int $points {
        get;
    }

    public float $hitsAvg {
        get;
        set;
    }

    public int $hitsSum {
        get;
        set;
    }

    public float $deathsAvg {
        get;
        set;
    }

    public int $deathsSum {
        get;
        set;
    }

    public float $scoreAvg {
        get;
        set;
    }

    public int $scoreSum {
        get;
        set;
    }

    public float $hitsOwnAvg {
        get;
        set;
    }

    public int $hitsOwnSum {
        get;
        set;
    }

    public float $deathsOwnAvg {
        get;
        set;
    }

    public int $deathsOwnSum {
        get;
        set;
    }

    public float $shotsAvg {
        get;
        set;
    }

    public int $shotsSum {
        get;
        set;
    }

    public float $missAvg {
        get;
        set;
    }

    public int $missSum {
        get;
        set;
    }

    public float $accuracyAvg {
        get;
        set;
    }

    public float $kd {
        get;
        set;
    }

	public function addColor(int|string $color) : void;

	public function addPlayer(GroupPlayerInterface ...$players) : void;
}