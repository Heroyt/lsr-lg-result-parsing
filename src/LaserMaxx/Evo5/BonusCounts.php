<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\LaserMaxx\Evo5;

use Dibi\Row;
use Lsr\Orm\Interfaces\InsertExtendInterface;

/**
 * Structure containing player's bonuses
 */
final class BonusCounts implements InsertExtendInterface
{
    public const array NAMES = [
        'agent'        => 'Agent',
        'invisibility' => 'Neviditelnost',
        'machineGun'   => 'Samopal',
        'shield'       => 'Štít',
    ];

    public function __construct(
        public int $agent = 0,
        public int $invisibility = 0,
        public int $machineGun = 0,
        public int $shield = 0,
    ) {}

    /**
     * @inheritDoc
     */
    public static function parseRow(Row $row) : static {
        return new static(
        /** @phpstan-ignore cast.int */
            (int)($row->bonus_agent ?? 0),
            /** @phpstan-ignore cast.int */
            (int)($row->bonus_invisibility ?? 0),
            /** @phpstan-ignore cast.int */
            (int)($row->bonus_machine_gun ?? 0),
            /** @phpstan-ignore cast.int */
            (int)($row->bonus_shield ?? 0),
        );
    }

    /**
     * Add data from the object into the data array for DB INSERT/UPDATE
     *
     * @param  array<string, mixed>  $data
     */
    public function addQueryData(array &$data) : void {
        $data['bonus_agent'] = $this->agent;
        $data['bonus_invisibility'] = $this->invisibility;
        $data['bonus_machine_gun'] = $this->machineGun;
        $data['bonus_shield'] = $this->shield;
    }

    /**
     * @return array<string,mixed>
     */
    public function getArray() : array {
        $data = [];
        $data['agent'] = $this->agent;
        $data['invisibility'] = $this->invisibility;
        $data['machine_gun'] = $this->machineGun;
        $data['shield'] = $this->shield;
        return $data;
    }

    public function getSum() : int {
        return $this->agent + $this->invisibility + $this->machineGun + $this->shield;
    }
}