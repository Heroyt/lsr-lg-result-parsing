<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\Interface\Models;

interface GroupTeamInterface
{
	public string $key {
		get;
	}

	public function getSkill(): float;

	public function getPoints(): int;

	public function getHitsAvg(): float;

	public function getHitsSum(): int;

	public function getDeathsAvg(): float;

	public function getDeathsSum(): int;

	public function getScoreAvg(): float;

	public function getScoreSum(): int;

	public function getHitsOwnAvg(): float;

	public function getHitsOwnSum(): int;

	public function getDeathsOwnAvg(): float;

	public function getDeathsOwnSum(): int;

	public function getShotsAvg(): float;

	public function getShotsSum(): int;

	public function getMissAvg(): float;

	public function getMissSum(): int;

	public function getAccuracyAvg(): float;

	public function getKd(): float;

	public function addColor(int|string $color) : void;

	public function addPlayer(GroupPlayerInterface ...$players) : void;
}