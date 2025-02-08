<?php
declare(strict_types=1);

namespace Lsr\Lg\Results;

use Lsr\Lg\Results\Collections\AbstractCollectionQuery;
use Lsr\Lg\Results\Interface\Models\TeamInterface;

/**
 * Query object for team models
 *
 * @template T of TeamInterface
 * @extends AbstractCollectionQuery<T>
 */
class TeamQuery extends AbstractCollectionQuery
{
}