<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\Interface\Models;

interface PlayerHitInterface extends \JsonSerializable
{

    public PlayerInterface $playerShot {
        get;
        set;
    }

    public PlayerInterface $playerTarget {
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