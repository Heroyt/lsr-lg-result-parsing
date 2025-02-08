<?php

/**
 * @author Tomáš Vojík <xvojik00@stud.fit.vutbr.cz>, <vojik@wboy.cz>
 */

namespace Lsr\Lg\Results\Exception;

use InvalidArgumentException;

/**
 * Exception thrown when trying to add an incorrect object to a collection
 */
class InvalidCollectionClassException extends InvalidArgumentException
{
}
