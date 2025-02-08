<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\LaserMaxx\Evo6;

use Lsr\Lg\Results\LaserMaxx\LaserMaxxPlayerInterface;

interface Evo6PlayerInterface extends LaserMaxxPlayerInterface
{

    public int $bonuses {
        get;
        set;
    }
    public int $calories {
        get;
        set;
    }

}