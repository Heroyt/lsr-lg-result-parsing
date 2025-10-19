<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\Interface\Models;

interface SoloGameModeInterface
{

    /**
     * @return class-string<TeamGameModeInterface>
     */
    public function getTeamAlternative(): string;

    /**
     * @template Team of TeamInterface
     * @template Player of PlayerInterface
     * @template Meta of array<string, mixed>
     * @template Game of GameInterface<Team, Player, Meta>
     * @param Game $game
     * @return Player|null
     */
    public function getWin(GameInterface $game): PlayerInterface|TeamInterface|null;

}