<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\LaserMaxx\Evo6;

use Dibi\Row;
use Lsr\Orm\Interfaces\InsertExtendInterface;

class RespawnSettings implements InsertExtendInterface
{

    public function __construct(
        public int $respawnLives = 0,
    ) {}

    /**
     * @inheritDoc
     */
    public static function parseRow(Row $row) : ?static {
        return new static(
            respawnLives: $row->respawn_lives,
        );
    }

    /**
     * @inheritDoc
     */
    public function addQueryData(array &$data) : void {
        $data['respawn_lives'] = $this->respawnLives;
    }
}