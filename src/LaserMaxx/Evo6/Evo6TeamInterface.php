<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\LaserMaxx\Evo6;

use Lsr\Lg\Results\LaserMaxx\LaserMaxxTeamInterface;

/**
 * @template Player of Evo6PlayerInterface
 * @template Game of Evo6GameInterface
 * @extends LaserMaxxTeamInterface<Player, Game>
 */
interface Evo6TeamInterface extends LaserMaxxTeamInterface
{

}