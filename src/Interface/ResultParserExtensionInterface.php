<?php

namespace Lsr\Lg\Results\Interface;

use Lsr\Lg\Results\AbstractResultsParser;
use Lsr\Lg\Results\Interface\Models\GameInterface;

interface ResultParserExtensionInterface
{
    /**
     * @template Game of GameInterface
     * @param Game $game
     * @param  array<string,mixed>  $meta
     * @param AbstractResultsParser<Game> $parser
     * @return void
     */
    public function parse(GameInterface $game, array $meta, AbstractResultsParser $parser) : void;
}
