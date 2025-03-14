<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\LaserMaxx;

use Lsr\Helpers\Tools\Strings;
use Lsr\Lg\Results\AbstractResultsGenerator;
use Lsr\Lg\Results\LaserMaxx\Evo6\Evo6GameInterface;

abstract class ResultsGenerator extends AbstractResultsGenerator
{
    public function getGroup(LaserMaxxGameInterface $game) : string {
        $meta = [
            'music'    => $game->music?->id,
            'mode'     => $game->modeName,
            'loadTime' => time(),
        ];
        $hashData = [];

        if ($game->group !== null) {
            $meta['group'] = $game->group->id;
            $meta['groupName'] = $game->group->name;
        }

        /** @var LaserMaxxPlayerInterface $player */
        foreach ($game->players as $player) {
            $vest = $player->vest;
            $asciiName = substr($this->escapeName($player->name), 0, 12);
            if ($player->name !== $asciiName) {
                $meta['p'.$vest.'n'] = $player->name;
            }
            if ($player->user !== null) {
                $meta['p'.$vest.'u'] = $player->user->getCode();
            }
            $hashData[(int) $vest] = $vest.'-'.$asciiName;
        }

        /** @var LaserMaxxTeamInterface $team */
        foreach ($game->teams as $team) {
            $asciiName = $this->escapeName($team->name);
            if ($team->name !== $asciiName) {
                $meta['t'.$team->color.'n'] = $team->name;
            }
        }

        ksort($hashData);
        $meta['hash'] = md5($meta['mode'].';'.implode(';', $hashData));

        if ($game->music !== null) {
            $meta['music'] = $game->music->id;
        }

        $metaString = json_encode($meta, JSON_THROW_ON_ERROR);
        $metaString = gzdeflate($metaString, 9);
        if (is_string($metaString)) {
            $metaString = gzdeflate($metaString, 9);
            if (is_string($metaString)) {
                $metaString = base64_encode($metaString);
            }
            else {
                $metaString = '';
            }
        }
        else {
            $metaString = '';
        }
        return 'GROUP{,'.$metaString.($game instanceof Evo6GameInterface ? ',2' : '').'}#';
    }

    /**
     * Replaces all unwanted characters in the player/team name
     *
     * @param  string  $name
     * @return string
     */
    protected function escapeName(string $name) : string {
        // Remove UTF-8 characters
        $name = Strings::toAscii($name);
        // Remove key characters
        return str_replace(
            [
                '#',
                ',',
                '}',
                '{',
            ],
            [
                '+',
                '.',
                ']',
                '[',
            ],
            $name
        );
    }

    public function getTeam(LaserMaxxGameInterface $game) : string {
        $teams = [];
        /** @var LaserMaxxTeamInterface $team */
        foreach ($game->teams as $team) {
            $teams[] = sprintf(
                'TEAM{%d,%s,%d}#',
                $team->color,
                $this->escapeName($team->name),
                $team->playerCount,
            );
        }
        return implode("\n", $teams);
    }

    public function getHits(LaserMaxxGameInterface $game) : string {
        $hits = [];
        /** @var LaserMaxxPlayerInterface $player */
        foreach ($game->players as $player) {
            $hit = 'HITS{'.$player->vest;
            /** @var LaserMaxxPlayerInterface $player2 */
            foreach ($game->players as $player2) {
                $hit .= ','.$player->getHitsPlayer($player2);
            }
            $hits[] = $hit.'}#';
        }
        return implode("\n", $hits);
    }

}