<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\LaserMaxx\Evo6;

use Dibi\Row;
use Lsr\Orm\Interfaces\InsertExtendInterface;

/**
 * @phpstan-consistent-constructor
 */
class ZombieSettings implements InsertExtendInterface
{

    public function __construct(
        public int  $lives = 0,
        public int  $ammo = 0,
        public int  $infectHits = 0,
        public bool $zombieSpecial = false,
        public int  $zombieTeamNumber = 0,
    ) {}

    /**
     * @inheritDoc
     */
    public static function parseRow(Row $row) : ?static {
        return new static(
            lives           : $row->zombie_lives,
            ammo            : $row->zombie_ammo,
            infectHits      : $row->zombie_infect_hits,
            zombieSpecial   : (bool) $row->zombie_special,
            zombieTeamNumber: $row->zombie_team_number,
        );
    }

    /**
     * @inheritDoc
     */
    public function addQueryData(array &$data) : void {
        $data['zombie_lives'] = $this->lives;
        $data['zombie_ammo'] = $this->ammo;
        $data['zombie_infect_hits'] = $this->infectHits;
        $data['zombie_special'] = $this->zombieSpecial;
        $data['zombie_team_number'] = $this->zombieTeamNumber;
    }
}