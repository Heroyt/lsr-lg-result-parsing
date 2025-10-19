<?php

/**
 * @author Tomáš Vojík <xvojik00@stud.fit.vutbr.cz>, <vojik@wboy.cz>
 */

namespace Lsr\Lg\Results\Interface\Collections;

use Lsr\Lg\Results\Collections\AbstractCollection;
use Lsr\Orm\Model;

/**
 * @template T of Model
 */
interface CollectionQueryInterface
{
    /**
     * Add a new filter to filter data by
     *
     * @param string $param
     * @param mixed ...$values
     *
     * @return $this
     */
    public function filter(string $param, mixed ...$values): CollectionQueryInterface;

    /**
     * Add any filter object
     *
     * @param CollectionQueryFilterInterface $filter
     *
     * @return $this
     */
    public function addFilter(CollectionQueryFilterInterface $filter): CollectionQueryInterface;

    /**
     * Get the query's result
     *
     * @return AbstractCollection<T>
     */
    public function get(): AbstractCollection;

    /**
     * Get only the first result or null
     *
     * @return T|null
     */
    public function first(): ?Model;

    /**
     * Set a parameter to sort the by result
     *
     * @param string $param
     *
     * @return $this
     */
    public function sortBy(string $param): CollectionQueryInterface;

    /**
     * Map the result to return an array of only given parameter
     *
     * @param string $param
     *
     * @return array<mixed>
     */
    public function pluck(string $param): array;

    /**
     * Add a map callback
     *
     * @template Mapped
     * @param callable(T):Mapped $callback
     *
     * @return array<Mapped>
     * @see array_map()
     */
    public function map(callable $callback): array;

    /**
     * Set sort direction in ascending order
     *
     * @return $this
     */
    public function asc(): CollectionQueryInterface;

    /**
     * Set sort direction in descending order
     *
     * @return $this
     */
    public function desc(): CollectionQueryInterface;
}
