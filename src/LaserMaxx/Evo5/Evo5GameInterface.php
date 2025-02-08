<?php

declare(strict_types=1);

namespace Lsr\Lg\Results\LaserMaxx\Evo5;

use Lsr\Lg\Results\LaserMaxx\LaserMaxxGameInterface;

interface Evo5GameInterface extends LaserMaxxGameInterface
{
    public Scoring $scoring {
        get;
        set;
    }

}
