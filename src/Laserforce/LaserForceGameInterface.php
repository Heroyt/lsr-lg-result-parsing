<?php

declare(strict_types=1);

namespace Lsr\Lg\Results\Laserforce;

use Lsr\Lg\Results\Interface\Models\GameInterface;
use Lsr\Lg\Results\Interface\Models\PlayerInterface;
use Lsr\Lg\Results\Interface\Models\TeamInterface;

/**
 * @template T of TeamInterface
 * @template P of PlayerInterface
 * @template M of array<string,mixed>
 * @extends GameInterface<T, P, M>
 */
interface LaserForceGameInterface extends GameInterface
{
}
