<?php

declare(strict_types=1);

namespace Lsr\Lg\Results\Interface\Models;

use Lsr\Lg\Results\Enums\GameModeType;

interface GameModeInterface
{
    public string $name {
        get;
        set;
    }
    public ?string $description {
        get;
        set;
    }
    public GameModeType $type {
        get;
        set;
    }
    public bool $rankable {
        get;
        set;
    }
    public bool $active {
        get;
        set;
    }


    public function isTeam() : bool;

    public function isSolo() : bool;

    public function getWin(GameInterface $game) : PlayerInterface | TeamInterface | null;

    public function recalculateScores(GameInterface $game) : void;

    public function reorderGame(GameInterface $game) : void;

    /**
     * @return class-string<GameModeInterface>
     */
    public function getSoloAlternative() : string;

    /**
     * @return class-string<GameModeInterface>
     */
    public function getTeamAlternative() : string;

    public function getName() : string;
}
