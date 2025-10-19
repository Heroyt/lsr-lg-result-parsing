<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\LaserMaxx\Evo5;

use Lsr\LaserLiga\PlayerInterface;
use Lsr\Lg\Results\LaserMaxx\LaserMaxxPlayerInterface;

/**
 * @template G of Evo5GameInterface
 * @template T of Evo5TeamInterface
 * @template U of PlayerInterface
 * @extends LaserMaxxPlayerInterface<G, T, U>
 */
interface Evo5PlayerInterface extends LaserMaxxPlayerInterface
{

    public BonusCounts $bonus {
        get;
        set;
    }

}