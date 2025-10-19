<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\Interface;

use Lsr\Lg\Results\Interface\Models\PlayerInterface;
use Lsr\Lg\Results\PlayerCollection;
use Lsr\Orm\Model;

/**
 * Interface for classes that have players
 *
 * @template P of PlayerInterface
 * @property class-string<P> $playerClass
 */
interface WithPlayersInterface
{

    public int $playerCount {
        get;
        set;
    }

    /** @var class-string<P> */
    public string $playerClass {
        get;
    }
    /** @var PlayerCollection<P&Model> */
    public PlayerCollection $players {
        get;
        set;
    }
    /** @var PlayerCollection<P&Model> */
    public PlayerCollection $playersSorted {
        get;
        set;
    }

    /**
     * @return PlayerCollection<P&Model>
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