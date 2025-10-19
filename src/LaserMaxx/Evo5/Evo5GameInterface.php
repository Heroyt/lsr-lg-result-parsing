<?php

declare(strict_types=1);

namespace Lsr\Lg\Results\LaserMaxx\Evo5;

use Lsr\Lg\Results\LaserMaxx\LaserMaxxGameInterface;

/**
 * @template T of Evo5TeamInterface
 * @template P of Evo5PlayerInterface
 * @template M of array<string,mixed>
 * @extends LaserMaxxGameInterface<T, P, M>
 */
interface Evo5GameInterface extends LaserMaxxGameInterface
{
    public Scoring $scoring {
        get;
        set;
    }

}
