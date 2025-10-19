<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\Interface;

use Lsr\Lg\Results\Interface\Models\TeamInterface;
use Lsr\Lg\Results\TeamCollection;
use Lsr\Orm\Model;

/**
 * @template T of TeamInterface
 * @property class-string<T> $teamClass
 */
interface WithTeamsInterface
{

    public int $teamCount {
        get;
    }

    /** @var class-string<T> */
    public string $teamClass {
        get;
    }
    /** @var TeamCollection<T&Model> */
    public TeamCollection $teams {
        get;
        set;
    }
    /** @var TeamCollection<T&Model> */
    public TeamCollection $teamsSorted {
        get;
        set;
    }

    /**
     * @return TeamCollection<T&Model>
     */
    public function loadTeams() : TeamCollection;

    /**
     * @param  T  ...$teams
     * @return $this
     */
    public function addTeam(TeamInterface ...$teams) : static;

    public function saveTeams() : bool;

}