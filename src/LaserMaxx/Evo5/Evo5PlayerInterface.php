<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\LaserMaxx\Evo5;

use Lsr\Lg\Results\LaserMaxx\LaserMaxxPlayerInterface;

interface Evo5PlayerInterface extends LaserMaxxPlayerInterface
{

    public BonusCounts $bonus {
        get;
        set;
    }

}