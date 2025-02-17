<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\Interface\Models;

interface GroupPlayerInterface
{

	public string $name {
		get;
		set;
	}

	public string $asciiName {
		get;
	}

	/** @var string[] */
	public array $gameCodes {
		get;
		set;
	}
	public int   $playCount {
		get;
		set;
	}

	public function getSumShots(): int;

	public function getAverageShots(): float;

	public function getAverageAccuracy(): float;

	public function getAverageHits(): float;

	public function getAverageDeaths(): float;

	public function getSumScore(): int;

	public function getAverageScore(): float;

	public function getSkill(): int;

	public function getFavouriteVest(): int;

	public function getKd(): float;

	public function getSumHits(): int;

	public function getSumDeaths(): int;

	public function getAverageOwnHits(): float;

	public function getSumOwnHits(): int;

	public function getAverageOwnDeaths(): float;

	public function getSumOwnDeaths(): int;

	public function getAverageMisses(): float;

	public function getMisses(): array;

	public function getSumMisses(): int;

}