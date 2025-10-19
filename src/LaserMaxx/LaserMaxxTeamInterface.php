<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\LaserMaxx;

use Lsr\Lg\Results\Interface\Models\TeamInterface;

/**
 * @template P of LaserMaxxPlayerInterface
 * @template G of LaserMaxxGameInterface
 * @extends TeamInterface<P, G>
 */
interface LaserMaxxTeamInterface extends TeamInterface
{

}