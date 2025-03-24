<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\LaserMaxx;

use Dibi\Row;
use Lsr\Lg\Results\LaserMaxx\Evo6\HitType;
use Lsr\Orm\Interfaces\InsertExtendInterface;

/**
 * @phpstan-consistent-constructor
 */
class VipSettings implements InsertExtendInterface
{

    public function __construct(
        public bool $on = false,
        public int     $lives = 0,
        public int     $ammo = 0,
        public int     $respawn = 5,
        public bool    $killTeam = false,
        public int     $vipHitScore = 0,
        public HitType $hitType = HitType::NORMAL,
        public bool    $blastShots = false,
        public bool    $ignoreTeammateHits = false,
    ) {}

    /**
     * @inheritDoc
     */
    public static function parseRow(Row $row) : ?static {
        return new static(
            on                : $row->vip_on,
            lives             : $row->vip_lives,
            ammo              : $row->vip_ammo,
            respawn           : $row->vip_respawn,
            killTeam          : (bool) $row->vip_kill_team,
            vipHitScore       : $row->vip_hit_score,
            hitType           : HitType::tryFrom($row->vip_hit_type) ?? HitType::NORMAL,
            blastShots        : (bool) $row->vip_blast_shots,
            ignoreTeammateHits: (bool) $row->vip_ignore_teammate_hits,
        );
    }

    /**
     * @inheritDoc
     */
    public function addQueryData(array &$data) : void {
        $data['vip_on'] = $this->on;
        $data['vip_lives'] = $this->lives;
        $data['vip_ammo'] = $this->ammo;
        $data['vip_respawn'] = $this->respawn;
        $data['vip_kill_team'] = $this->killTeam;
        $data['vip_hit_score'] = $this->vipHitScore;
        $data['vip_hit_type'] = $this->hitType->value;
        $data['vip_blast_shots'] = $this->blastShots;
        $data['vip_ignore_teammate_hits'] = $this->ignoreTeammateHits;
    }
}