<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\Interface\Models;

/**
 * @template P of PlayerInterface
 * @property P $playerTarget
 * @property P $playerShot
 */
interface PlayerHitInterface extends \JsonSerializable
{

    /** @var P */
    public PlayerInterface $playerTarget {
        get;
        set;
    }

    /** @var P */
    public PlayerInterface $playerShot {
        get;
        set;
    }

    public int $count {
        get;
        set;
    }

    public function save() : bool;

    /**
     * @return array{id_player:int|null,id_target:int|null,count:int|null}
     */
    public function getQueryData() : array;

}