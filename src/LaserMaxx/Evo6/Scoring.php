<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\LaserMaxx\Evo6;

use Dibi\Row;
use Lsr\Lg\Results\LaserMaxx\AccuracyBonus;
use Lsr\Orm\Interfaces\InsertExtendInterface;

/**
 * Structure containing game's scoring settings
 *
 * Scoring = how many points does a player get for an action.
 *
 * @phpstan-consistent-constructor
 */
class Scoring implements InsertExtendInterface
{
	public function __construct(
		public int                $deathOther = 0,
		public int                $hitOther = 0,
		public int                $deathOwn = 0,
		public int                $hitOwn = 0,
		public int                $hitPod = 0,
		public int                $shot = 0,
		public int                $machineGun = 0,
		public int                $invisibility = 0,
		public int                $agent = 0,
		public int                $shield = 0,
		public int                $highscore = 0,
		public AccuracyBonus      $accuracyBonus = AccuracyBonus::OFF,
		public int                $accuracyThreshold = 0,
		public int                $accuracyThresholdBonus = 0,
		public EncouragementBonus $encouragementBonus = EncouragementBonus::OFF,
		public int                $encouragementBonusScore = 0,
		public int                $power = 0,
		public int                $penalty = 0,
		public int                $activity = 0,
		public int                $knockout = 0,
	) {
	}

	public static function parseRow(Row $row): static {
		return new static(
			$row->scoring_death_other ?? 0,
			$row->scoring_hit_other ?? 0,
			$row->scoring_death_own ?? 0,
			$row->scoring_hit_own ?? 0,
			$row->scoring_hit_pod ?? 0,
			$row->scoring_shot ?? 0,
			$row->scoring_power_machine_gun ?? 0,
			$row->scoring_power_invisibility ?? 0,
			$row->scoring_power_agent ?? 0,
			$row->scoring_power_shield ?? 0,
			$row->highscore ?? 0,
			AccuracyBonus::tryFrom($row->scoring_accuracy_bonus ?? 0) ?? AccuracyBonus::OFF,
			$row->scoring_accuracy_threshold ?? 0,
			$row->scoring_accuracy_threshold_bonus ?? 0,
			EncouragementBonus::tryFrom($row->scoring_encouragement_bonus ?? 0) ?? EncouragementBonus::OFF,
			$row->scoring_encouragement_bonus_score ?? 0,
			$row->scoring_power ?? 0,
			$row->scoring_penalty ?? 0,
			$row->scoring_activity ?? 0,
			$row->scoring_knockout ?? 0,
		);
	}

	/**
	 * @param array<string,mixed> $data
	 *
	 * @return void
	 */
	public function addQueryData(array &$data): void {
		$data['scoring_hit_other'] = $this->hitOther;
		$data['scoring_hit_own'] = $this->hitOwn;
		$data['scoring_death_other'] = $this->deathOther;
		$data['scoring_death_own'] = $this->hitOwn;
		$data['scoring_hit_pod'] = $this->hitPod;
		$data['scoring_shot'] = $this->shot;
		$data['scoring_power_machine_gun'] = $this->machineGun;
		$data['scoring_power_invisibility'] = $this->invisibility;
		$data['scoring_power_agent'] = $this->agent;
		$data['scoring_power_shield'] = $this->shield;
		$data['scoring_highscore'] = $this->highscore;
		$data['scoring_accuracy_bonus'] = $this->accuracyBonus->value;
		$data['scoring_accuracy_threshold'] = $this->accuracyThreshold;
		$data['scoring_accuracy_threshold_bonus'] = $this->accuracyThresholdBonus;
		$data['scoring_encouragement_bonus'] = $this->encouragementBonus->value;
		$data['scoring_encouragement_bonus_score'] = $this->encouragementBonusScore;
		$data['scoring_power'] = $this->power;
		$data['scoring_penalty'] = $this->penalty;
		$data['scoring_activity'] = $this->activity;
		$data['scoring_knockout'] = $this->knockout;
	}
}
