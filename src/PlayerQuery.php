<?php
declare(strict_types=1);

namespace Lsr\Lg\Results;

use Lsr\Lg\Results\Interface\Models\PlayerInterface;
use Lsr\Orm\Model;

/**
 * Query object for player models
 *
 * @template P of PlayerInterface&Model
 *
 * @extends Collections\AbstractCollectionQuery<P>
 */
class PlayerQuery extends Collections\AbstractCollectionQuery
{

}