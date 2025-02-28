<?php

/**
 * @author Tomáš Vojík <xvojik00@stud.fit.vutbr.cz>, <vojik@wboy.cz>
 */

namespace Lsr\Lg\Results\Interface;

use Lsr\Exceptions\FileException;
use Lsr\Lg\Results\Interface\Models\GameInterface;

/**
 * Interface for all result parsers
 *
 * @template G of GameInterface
 */
interface ResultsParserInterface
{
    /**
     * Get result file pattern for lookup
     *
     * @return string
     */
    public static function getFileGlob() : string;

    /**
     * Check if given result file should be parsed by this parser.
     *
     * @param  string  $fileName
     * @param  string  $contents
     * @return bool True if this parser can parse this game file
     * @pre File exists
     * @pre File is readable
     *
     */
    public static function checkFile(string $fileName = '', string $contents = '') : bool;

    /**
     * @param  string  $fileName
     *
     * @return $this
     * @throws FileException
     */
    public function setFile(string $fileName) : static;

    /**
     * @param  string  $contents
     *
     * @return $this
     */
    public function setContents(string $contents) : static;

    /**
     * Parse a game results file and return a parsed object
     *
     * @return GameInterface
     */
    public function parse() : GameInterface;
}
