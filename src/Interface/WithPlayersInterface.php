<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\Interface;

use Lsr\Lg\Results\Interface\Models\PlayerInterface;
use Lsr\Lg\Results\PlayerCollection;

/**
 * Interface for classes that have players
 *
 * @template P of PlayerInterface
 */
interface WithPlayersInterface
{

    public int $playerCount {
        get;
    }

    /** @var class-string<P> */
    public string $playerClass {
        get;
    }
    public PlayerCollection $players {
        get;
        set;
    }
    public PlayerCollection $playersSorted {
        get;
        set;
    }

    /**
     * @return PlayerCollection<P>
     */
    public function loadPlayers() : PlayerCollection;

    public function getMinScore() : int;

    public function getMaxScore() : int;

    /**
     * @param  P  ...$players
     * @return $this
     */
    public function addPlayer(PlayerInterface ...$players) : static;

    public function savePlayers() : bool;


}