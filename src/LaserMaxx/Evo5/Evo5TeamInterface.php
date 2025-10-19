<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\LaserMaxx\Evo5;

use Lsr\Lg\Results\LaserMaxx\LaserMaxxTeamInterface;

/**
 * @template Player of Evo5PlayerInterface
 * @template Game of Evo5GameInterface
 * @extends LaserMaxxTeamInterface<Player, Game>
 */
interface Evo5TeamInterface extends LaserMaxxTeamInterface
{

}