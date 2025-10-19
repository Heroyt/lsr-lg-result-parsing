<?php
declare(strict_types=1);

namespace Lsr\Lg\Results;

use Lsr\Lg\Results\Interface\Collections\CollectionQueryInterface;
use Lsr\Lg\Results\Interface\Models\TeamInterface;
use Lsr\Orm\Model;

/**
 * A collection for team models
 *
 * @template T of TeamInterface&Model
 *
 * @property T[] $data
 *
 * @extends Collections\AbstractCollection<T>
 */
class TeamCollection extends Collections\AbstractCollection
{
    /**
     * @var class-string<T>
     * @phpstan-ignore property.defaultValue
     */
    public string $type = TeamInterface::class;

    /**
     * @inheritDoc
     */
    public function query() : CollectionQueryInterface {
        /** @var TeamQuery<T> $query */
        $query = new TeamQuery($this);
        return $query;
    }
}