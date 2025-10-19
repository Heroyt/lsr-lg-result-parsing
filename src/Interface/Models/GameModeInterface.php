<?php

declare(strict_types=1);

namespace Lsr\Lg\Results\Interface\Models;

use Lsr\Lg\Results\Enums\GameModeType;

interface GameModeInterface extends ModelInterface
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


    /**
     * @return bool
     * @phpstan-assert-if-true TeamGameModeInterface $this
     */
    public function isTeam() : bool;

    /**
     * @return bool
     * @phpstan-assert-if-true SoloGameModeInterface $this
     */
    public function isSolo() : bool;

    /**
     * @template Player of PlayerInterface
     * @template Team of TeamInterface
     * @template Meta of array<string, mixed>
     * @template Game of GameInterface<Team, Player, Meta>
     * @param Game $game
     * @return void
     */
    public function recalculateScores(GameInterface $game) : void;

    /**
     * @template Player of PlayerInterface
     * @template Team of TeamInterface
     * @template Meta of array<string, mixed>
     * @template Game of GameInterface<Team, Player, Meta>
     * @param Game $game
     * @return void
     */
    public function reorderGame(GameInterface $game) : void;

    public function getName() : string;
}
