<?php

/**
 * @author Tomáš Vojík <xvojik00@stud.fit.vutbr.cz>, <vojik@wboy.cz>
 */

namespace Lsr\Lg\Results\Collections;

use Lsr\Lg\Results\Enums\SortType;
use Lsr\Lg\Results\Exception\InvalidQueryParameterException;
use Lsr\Lg\Results\Interface\Collections\CollectionInterface;
use Lsr\Lg\Results\Interface\Collections\CollectionQueryFilterInterface;
use Lsr\Lg\Results\Interface\Collections\CollectionQueryInterface;
use Lsr\Orm\Model;
use Nette\Utils\Strings;

/**
 * @template T of Model
 * @implements CollectionQueryInterface<T>
 */
abstract class AbstractCollectionQuery implements CollectionQueryInterface
{
    /** @var CollectionQueryFilterInterface[] */
    protected array $filters = [];
    protected string $sortBy = '';
    protected SortType $sortDirection = SortType::ASC;

    /**
     * @param AbstractCollection<T> $collection
     */
    public function __construct(
        protected AbstractCollection $collection
    )
    {
    }

    /**
     * Get only the first result or null
     *
     * @return T|null
     */
    public function first(): ?Model
    {
        /** @noinspection LoopWhichDoesNotLoopInspection */
        foreach ($this->get() as $data) {
            return $data;
        }
        return null;
    }

    /**
     * Get the result of the query
     *
     * @return AbstractCollection<T>
     */
    public function get(): AbstractCollection
    {
        $collection = clone $this->collection;
        $this
            ->applyFilters($collection)
            ->sort($collection);
        return $collection;
    }


    /**
     * @param CollectionInterface<T> $collection
     *
     * @return $this
     * @pre AbstractCollectionQuery::$sortBy must be validated to exist before
     */
    protected function sort(CollectionInterface $collection): AbstractCollectionQuery
    {
        if (empty($this->sortBy)) {
            return $this;
        }
        if (method_exists($this->getType(), $this->sortBy)) {
            $collection->sort(
                function (Model $modelA, Model $modelB) {
                    $paramA = $modelA->{$this->sortBy}();
                    $paramB = $modelB->{$this->sortBy}();
                    return $this->compare($paramA, $paramB);

                }
            );
        } elseif (property_exists($this->getType(), $this->sortBy)) {
            $collection->sort(
                function (Model $modelA, Model $modelB) {
                    $paramA = $modelA->{$this->sortBy};
                    $paramB = $modelB->{$this->sortBy};
                    return $this->compare($paramA, $paramB);
                }
            );
        }
        return $this;
    }

    /**
     * @return string
     */
    protected function getType(): string
    {
        return $this->collection->getType();
    }

    protected function compare(mixed $paramA, mixed $paramB): int|float
    {
        if (is_numeric($paramA) && is_numeric($paramB)) {
            return $this->sortDirection === SortType::ASC ? $paramA - $paramB : $paramB - $paramA;
        }
        if (is_string($paramA) && is_string($paramB)) {
            return $this->sortDirection === SortType::ASC ? strcmp($paramA, $paramB) :
                strcmp($paramB, $paramA);
        }
        throw new InvalidQueryParameterException(
            'Invalid orderBy type ' . gettype($paramA) . '. Sort expects numeric or string values.'
        );
    }

    /**
     * @param CollectionInterface<T> $collection
     *
     * @return $this
     */
    protected function applyFilters(CollectionInterface $collection): AbstractCollectionQuery
    {
        foreach ($this->filters as $filer) {
            $filer->apply($collection);
        }
        return $this;
    }

    /**
     * @param string $param
     *
     * @return CollectionQueryInterface<T>
     */
    public function sortBy(string $param): CollectionQueryInterface
    {
        $method = 'get' . Strings::firstUpper($param);
        if (method_exists($this->getType(), $method)) {
            $this->sortBy = $method;
            return $this;
        }
        if (property_exists($this->getType(), $param)) {
            $this->sortBy = $param;
            return $this;
        }
        throw new InvalidQueryParameterException(
            'Invalid query parameter. Neither ' . $this->getType() . '::$' . $param . ' or ' . $this->getType() . '::' . $method . '() does not exist.'
        );
    }

    /**
     * @param string $param
     * @param mixed ...$values
     *
     * @return CollectionQueryInterface<T>
     */
    public function filter(string $param, ...$values): CollectionQueryInterface
    {
        if (property_exists($this->getType(), $param)) {
            $this->filters[] = new CollectionQueryFilter($param, $values);
            return $this;
        }
        $method = 'get' . Strings::firstUpper($param);
        if (method_exists($this->getType(), $method)) {
            $this->filters[] = new CollectionQueryFilter($method, $values, true);
            return $this;
        }
        throw new InvalidQueryParameterException(
            'Invalid query parameter. Neither ' . $this->getType() . '::$' . $param . ' or ' . $this->getType() . '::' . $method . '() does not exist.'
        );
    }

    /**
     * Add any filter object
     *
     * @param CollectionQueryFilterInterface $filter
     *
     * @return CollectionQueryInterface<T>
     */
    public function addFilter(CollectionQueryFilterInterface $filter): CollectionQueryInterface
    {
        $this->filters[] = $filter;
        return $this;
    }

    public function pluck(string $param): array
    {
        if (property_exists($this->getType(), $param)) {
            return $this->map(static fn(Model $model) => $model->$param);
        }
        $method = 'get' . Strings::firstUpper($param);
        if (method_exists($this->getType(), $method)) {
            return $this->map(static fn(Model $model) => $model->$method());
        }
        throw new InvalidQueryParameterException(
            'Invalid query parameter. Neither ' . $this->getType() . '::$' . $param . ' or ' . $this->getType() . '::' . $method . '() does not exist.'
        );
    }

    public function map(callable $callback): array
    {
        return array_map($callback, $this->get()->getAll());
    }

    /**
     * Set sort direction in ascending order
     *
     * @return CollectionQueryInterface<T>
     */
    public function asc(): CollectionQueryInterface
    {
        $this->sortDirection = SortType::ASC;
        return $this;
    }

    /**
     * Set sort direction in descending order
     *
     * @return CollectionQueryInterface<T>
     */
    public function desc(): CollectionQueryInterface
    {
        $this->sortDirection = SortType::DESC;
        return $this;
    }
}
