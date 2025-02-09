<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\LaserMaxx\Evo6;

use Dibi\Row;
use Lsr\Orm\Interfaces\InsertExtendInterface;

class HitGainSettings implements InsertExtendInterface
{

    public function __construct(
        public int $ammo = 0,
        public int $lives = 0,
    ) {}

    /**
     * @inheritDoc
     */
    public static function parseRow(Row $row) : ?static {
        return new static(
            ammo : $row->hit_gain_ammo,
            lives: $row->hit_gain_lives,
        );
    }

    /**
     * @inheritDoc
     */
    public function addQueryData(array &$data) : void {
        $data['hit_gain_ammo'] = $this->ammo;
        $data['hit_gain_lives'] = $this->lives;
    }
}