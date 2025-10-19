<?php
declare(strict_types=1);

namespace Lsr\Lg\Results;

use Lsr\Lg\Results\Interface\Collections\CollectionQueryInterface;
use Lsr\Lg\Results\Interface\Models\PlayerInterface;
use Lsr\Orm\Model;

/**
 * A collection for player models
 *
 * @template P of PlayerInterface&Model
 *
 * @property P[] $data
 *
 * @extends Collections\AbstractCollection<P>
 */
class PlayerCollection extends Collections\AbstractCollection
{
    /**
     * @var class-string<P>
     * @phpstan-ignore property.defaultValue
     */
    protected string $type = PlayerInterface::class;

    /**
     * @return PlayerQuery<P>
     */
    public function query() : CollectionQueryInterface {
        /** @var PlayerQuery<P> $query */
        $query = new PlayerQuery($this);
        return $query;
    }
}