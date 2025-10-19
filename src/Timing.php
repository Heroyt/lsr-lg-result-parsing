<?php

/**
 * @author Tomáš Vojík <xvojik00@stud.fit.vutbr.cz>, <vojik@wboy.cz>
 */

namespace Lsr\Lg\Results;

use Dibi\Row;
use Lsr\Orm\Interfaces\InsertExtendInterface;

/**
 * Game's timing settings
 *
 * @phpstan-consistent-constructor
 */
class Timing implements InsertExtendInterface
{
    /**
     * @param  int  $before  Seconds before game
     * @param  int  $gameLength  Game length in minutes
     * @param  int  $after  Seconds after game
     */
    public function __construct(
        public int $before = 0,
        public int $gameLength = 0,
        public int $after = 0,
    ) {}

    public static function parseRow(Row $row) : static {
        return new static(
        /** @phpstan-ignore cast.int */
            (int)($row->timing_before ?? 0),
            /** @phpstan-ignore cast.int */
            (int)($row->timing_game_length ?? 0),
            /** @phpstan-ignore cast.int */
            (int)($row->timing_after ?? 0),
        );
    }

    /**
     * @param  array<string,mixed>  $data
     *
     * @return void
     */
    public function addQueryData(array &$data) : void {
        $data['timing_before'] = $this->before;
        $data['timing_game_length'] = $this->gameLength;
        $data['timing_after'] = $this->after;
    }
}
