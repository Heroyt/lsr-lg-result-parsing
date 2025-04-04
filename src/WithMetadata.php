<?php

namespace Lsr\Lg\Results;

use JsonException;
use Lsr\LaserLiga\PlayerProviderInterface;
use Lsr\Lg\Results\Interface\Models\GameInterface;
use Lsr\Orm\Exceptions\ModelNotFoundException;
use Lsr\Orm\Exceptions\ValidationException;

/**
 * Helper methods for decoding and parsing metadata for a Game
 */
trait WithMetadata
{
    /** @var int 5 minutes in seconds */
    protected const int MAX_LOAD_START_TIME_DIFFERENCE = 300;

    protected readonly PlayerProviderInterface $playerProvider;

    /**
     * Decode game metadata
     *
     * @return array<string,string|numeric>
     */
    protected function decodeMetadata(string $encoded) : array {
        $encoded = trim($encoded);
        if ($encoded === '') {
            return [];
        }
        /** @var string|false $decodedJson */
        $decodedJson = @base64_decode($encoded);
        if ($decodedJson === false) {
            return [];
        }
        /** @var string|false $decodedJson */
        $decodedJson = @gzinflate((string) $decodedJson);
        if ($decodedJson === false) {
            return [];
        }
        /** @var string|false $decodedJson */
        $decodedJson = @gzinflate((string) $decodedJson);
        if ($decodedJson === false) {
            return [];
        }
        try {
            return json_decode($decodedJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            // Ignore meta
        }
        return [];
    }

    /**
     * Check if metadata corresponds with the parsed game
     *
     * @param  array<string, string|numeric>  $meta
     * @param  Game  $game
     *
     * @post Sets metadata (Game::setMeta()) to the game if valid.
     *
     * @return bool
     */
    protected function validateMetadata(array $meta, GameInterface $game) : bool {
        if (empty($meta)) {
            return false;
        }

        if (!empty($meta['hash'])) {
            $players = [];
            foreach ($game->players as $player) {
                $players[(int) $player->vest] = $player->vest.'-'.$player->name;
            }
            ksort($players);
            // Calculate and compare hash
            $hash = md5(strtolower($game->modeName).';'.implode(';', $players));
            if ($hash === $meta['hash']) {
                $game->setMeta($meta);
                return true;
            }
            if (!empty($meta['mode'])) {
                // Game modes must match
                if (strtolower($meta['mode']) !== strtolower($game->modeName)) {
                    return false;
                }

                // Compare load time with game start time
                if (!empty($meta['loadTime'])) {
                    $loadTime = (int) $meta['loadTime'];
                    $startTime = $game->start?->getTimestamp() ?? 0;
                    $diff = $startTime - $loadTime;
                    if ($diff > 0 && $diff < $this::MAX_LOAD_START_TIME_DIFFERENCE) {
                        $game->setMeta($meta);
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Set music mode information for the game from metadata
     *
     * @param  Game  $game
     * @param  array<string,string|numeric>  $meta
     *
     * @pre Metadata is validated
     * @post
     *
     * @return void
     */
    protected function setMusicModeFromMeta(GameInterface $game, array $meta) : void {
        if (empty($meta['music']) || ((int) $meta['music']) < 1) {
            return;
        }

        try {
            $game->music = ($this::MUSIC_CLASS)::get((int) $meta['music']);
        } catch (ModelNotFoundException | ValidationException) {
            // Ignore
            $game->music = null;
        }
    }

    /**
     * Set group information for the game from metadata
     *
     * @param  Game  $game
     * @param  array<string,string|numeric>  $meta
     *
     * @pre  Metadata is validated
     * @post The group is set on the Game object. If necessary, the new group is created
     *
     * @return void
     */
    protected function setGroupFromMeta(GameInterface $game, array $meta) : void {
        if (empty($meta['group'])) {
            return;
        }

        if ($meta['group'] !== 'new') {
            try {
                // Find existing group
                $group = ($this::GAME_GROUP_CLASS)::get((int) $meta['group']);
                // If found, clear its players cache to account for the newly-added (imported) game
                $group->clearCache();
            } catch (ModelNotFoundException | ValidationException) {
                // Ignore
            }
        }

        // Default to creating a new game group if the group was not found
        if (!isset($group)) {
            $group = new ($this::GAME_GROUP_CLASS)();
            $group->name = sprintf(
                'Skupina %s',
                isset($game->start) ? $game->start->format('d.m.Y H:i') : ''
            );
        }

        $game->group = $group;
    }

    /**
     * Set all player information from metadata
     *
     * @param  Game  $game
     * @param  array<string,string|numeric>  $meta
     *
     * @pre  Metadata is validated
     * @post All players have their names set in UTF-8
     * @post All players have their user profiles set
     *
     * @return void
     */
    protected function setPlayersMeta(GameInterface $game, array $meta) : void {
        /** @var Player $player */
        foreach ($game->players as $player) {
            // Names from game are strictly ASCII
            // If a name contained any non ASCII character, it is coded in the metadata
            if (!empty($meta['p'.$player->vest.'n']) && is_string($meta['p'.$player->vest.'n'])) {
                $player->name = $meta['p'.$player->vest.'n'];
            }

            // Check for player's user code
            if (!empty($meta['p'.$player->vest.'u'])) {
                $code = $meta['p'.$player->vest.'u'];
                assert(is_string($code));
                $user = ($this::USER_CLASS)::getByCode($code);

                // Check the public API for user by code
                if (!isset($user)) {
                    $user = $this->playerProvider->findPublicPlayerByCode($code);
                    try {
                        if (isset($user) && !$user->save()) {
                            // User found, but the save failed
                            $user = null;
                        }
                    } catch (ValidationException) {
                        $user = null;
                    }
                }
                if (isset($user)) {
                    $player->user = $user;
                }
            }
        }
    }

    /**
     * Set all team information from metadata
     *
     * @param  Game  $game
     * @param  array<string,string|numeric>  $meta
     *
     * @pre  Metadata is validated
     * @post All teams have their names set in UTF-8
     *
     * @return void
     */
    protected function setTeamsMeta(GameInterface $game, array $meta) : void {
        /** @var Team $team */
        foreach ($game->teams as $team) {
            // Names from game are strictly ASCII
            // If a name contained any non ASCII character, it is coded in the metadata
            if (!empty($meta['t'.$team->color.'n'])) {
                $team->name = (string) $meta['t'.$team->color.'n'];
            }
        }
    }
}
