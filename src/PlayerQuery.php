<?php
declare(strict_types=1);

namespace Lsr\Lg\Results;

use Lsr\Lg\Results\Interface\Models\PlayerInterface;

/**
 * Query object for player models
 *
 * @template P of PlayerInterface
 *
 * @extends Collections\AbstractCollectionQuery<P>
 */
class PlayerQuery extends Collections\AbstractCollectionQuery
{

}