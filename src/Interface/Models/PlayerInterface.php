<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\Interface\Models;

use Lsr\LaserLiga\PlayerInterface as UserInterface;
use Lsr\Lg\Results\Interface\WithGameInterface;

/**
 * @template G of GameInterface
 * @template T of TeamInterface
 * @template U of UserInterface
 * @extends WithGameInterface<G>
 */
interface PlayerInterface extends ModelInterface, WithGameInterface
{

    public string $name {
        get;
        set;
    }

    public int $score {
        get;
        set;
    }

    public int $position {
        get;
        set;
    }

    public int $skill {
        get;
        set;
    }

    public int $shots {
        get;
        set;
    }

    public int | string $vest {
        get;
        set;
    }

    public int $accuracy {
        get;
        set;
    }

    public int $hits {
        get;
        set;
    }

    public int $deaths {
        get;
        set;
    }

    /** @var T|null */
    public ?TeamInterface $team {
        get;
        set;
    }

    /** @var U|null */
    public ?UserInterface $user {
        get;
        set;
    }

    /** @var PlayerHitInterface<static>[]|null */
    public ?array $hitPlayers {
        get;
        set;
    }
    public int $teamNum {
        get;
        set;
    }
    public int $color {
        get;
    }
    public int $miss {
        get;
    }
    /** @var static|null */
    public ?PlayerInterface $favouriteTarget {
        get;
        set;
    }
    /** @var static|null */
    public ?PlayerInterface $favouriteTargetOf {
        get;
        set;
    }
    public ?float $relativeHits {
        get;
        set;
    }
    public ?float $relativeDeaths {
        get;
        set;
    }

    public function saveHits() : bool;

    /**
     * @return PlayerHitInterface<static>[]
     */
    public function loadHits() : array;

    /**
     * @return PlayerHitInterface<static>[]
     */
    public function getHitsPlayers() : array;

    /**
     * @param static $player
     * @param int $count
     * @return $this
     */
    public function addHits(PlayerInterface $player, int $count = 1) : static;

    /**
     * @param static $player
     * @return int
     */
    public function getHitsPlayer(PlayerInterface $player) : int;


    /**
     * Calculate a skill level based on the player's results
     *
     * The skill value aims to better evaluate the player's play style than the regular score value.
     * It should take multiple metrics into account.
     * Other LG system implementations should modify this function to calculate the value based on its specific metrics.
     * The value must be normalized based on the game's length.
     *
     * @pre The player's results should be set.
     *
     * @return int A whole number evaluation on an arbitrary scale (no max or min value).
     */
    public function calculateSkill() : int;

    public function getKd() : float;

    public function getExpectedAverageHitCount() : float;

    public function getExpectedAverageTeammateHitCount() : float;

    /**
     * @return array<string, float>
     */
    public function getSkillParts() : array;

}