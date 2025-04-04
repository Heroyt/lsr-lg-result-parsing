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
    /** @var CollectionQueryFilterInterface<T>[] */
    protected array $filters = [];
    protected string $sortBy = '';
    protected SortType $sortDirection = SortType::ASC;
    /** @var callable|null */
    protected $mapCallback;

    /**
     * @param  AbstractCollection<T>  $collection
     */
    public function __construct(
        protected AbstractCollection $collection
    ) {}

    /**
     * Get only the first result or null
     *
     * @return T|null
     */
    public function first() : ?Model {
        /** @noinspection LoopWhichDoesNotLoopInspection */
        foreach ($this->get() as $data) {
            return $data;
        }
        return null;
    }

    /**
     * Get the result of the query
     *
     * @return AbstractCollection<T>|array<T>
     */
    public function get() : AbstractCollection | array {
        $collection = clone $this->collection;
        $this
            ->applyFilters($collection)
            ->sort($collection);
        if (isset($this->mapCallback)) {
            $data = $collection->getAll();
            return array_map($this->mapCallback, $data);
        }
        return $collection;
    }

    /**
     * @param  CollectionInterface<T>  $collection
     *
     * @return $this
     * @pre AbstractCollectionQuery::$sortBy must be validated to exist before
     */
    protected function sort(CollectionInterface $collection) : AbstractCollectionQuery {
        if (empty($this->sortBy)) {
            return $this;
        }
        if (method_exists($this->getType(), $this->sortBy)) {
            $collection->sort(
                function (Model $modelA, Model $modelB) {
                    $paramA = $modelA->{$this->sortBy}();
                    $paramB = $modelB->{$this->sortBy}();
                    if (is_numeric($paramA)) {
                        return $this->sortDirection === SortType::ASC ? $paramA - $paramB : $paramB - $paramA;
                    }
                    if (is_string($paramA)) {
                        return $this->sortDirection === SortType::ASC ? strcmp($paramA, $paramB) :
                            strcmp($paramB, $paramA);
                    }
                    throw new InvalidQueryParameterException(
                        'Invalid orderBy type '.gettype($paramA).'. Sort expects numeric or string values.'
                    );

                }
            );
        }
        else {
            if (property_exists($this->getType(), $this->sortBy)) {
                $collection->sort(
                    function (Model $modelA, Model $modelB) {
                        $paramA = $modelA->{$this->sortBy};
                        $paramB = $modelB->{$this->sortBy};
                        if (is_numeric($paramA)) {
                            return $this->sortDirection === SortType::ASC ? $paramA - $paramB : $paramB - $paramA;
                        }
                        if (is_string($paramA)) {
                            return $this->sortDirection === SortType::ASC ? strcmp($paramA, $paramB) :
                                strcmp($paramB, $paramA);
                        }
                        throw new InvalidQueryParameterException(
                            'Invalid orderBy type '.gettype($paramA).'. Sort expects numeric or string values.'
                        );
                    }
                );
            }
        }
        return $this;
    }

    /**
     * @return string
     */
    protected function getType() : string {
        return $this->collection->getType();
    }

    /**
     * @param  CollectionInterface<T>  $collection
     *
     * @return $this
     */
    protected function applyFilters(CollectionInterface $collection) : AbstractCollectionQuery {
        foreach ($this->filters as $filer) {
            $filer->apply($collection);
        }
        return $this;
    }

    /**
     * @param  string  $param
     *
     * @return CollectionQueryInterface<T>
     */
    public function sortBy(string $param) : CollectionQueryInterface {
        $method = 'get'.Strings::firstUpper($param);
        if (method_exists($this->getType(), $method)) {
            $this->sortBy = $method;
            return $this;
        }
        if (property_exists($this->getType(), $param)) {
            $this->sortBy = $param;
            return $this;
        }
        throw new InvalidQueryParameterException(
            'Invalid query parameter. Neither '.$this->getType().'::$'.$param.' or '.$this->getType(
            ).'::'.$method.'() does not exist.'
        );
    }

    /**
     * @param  string  $param
     * @param  mixed  ...$values
     *
     * @return CollectionQueryInterface<T>
     */
    public function filter(string $param, ...$values) : CollectionQueryInterface {
        if (property_exists($this->getType(), $param)) {
            $this->filters[] = new CollectionQueryFilter($param, $values);
            return $this;
        }
        $method = 'get'.Strings::firstUpper($param);
        if (method_exists($this->getType(), $method)) {
            $this->filters[] = new CollectionQueryFilter($method, $values, true);
            return $this;
        }
        throw new InvalidQueryParameterException(
            'Invalid query parameter. Neither '.$this->getType().'::$'.$param.' or '.$this->getType(
            ).'::'.$method.'() does not exist.'
        );
    }

    /**
     * Add any filter object
     *
     * @param  CollectionQueryFilterInterface<T>  $filter
     *
     * @return CollectionQueryInterface<T>
     */
    public function addFilter(CollectionQueryFilterInterface $filter) : CollectionQueryInterface {
        $this->filters[] = $filter;
        return $this;
    }

    /**
     * @param  string  $param
     *
     * @return CollectionQueryInterface<T>
     */
    public function pluck(string $param) : CollectionQueryInterface {
        if (property_exists($this->getType(), $param)) {
            $this->mapCallback = static function (Model $model) use ($param) {
                return $model->$param;
            };
            return $this;
        }
        $method = 'get'.Strings::firstUpper($param);
        if (method_exists($this->getType(), $method)) {
            $this->mapCallback = static function (Model $model) use ($method) {
                return $model->$method();
            };
            return $this;
        }
        throw new InvalidQueryParameterException(
            'Invalid query parameter. Neither '.$this->getType().'::$'.$param.' or '.$this->getType(
            ).'::'.$method.'() does not exist.'
        );
    }

    /**
     * @param  callable  $callback
     *
     * @return CollectionQueryInterface<T>
     */
    public function map(callable $callback) : CollectionQueryInterface {
        $this->mapCallback = $callback;
        return $this;
    }

    /**
     * Set sort direction in ascending order
     *
     * @return CollectionQueryInterface<T>
     */
    public function asc() : CollectionQueryInterface {
        $this->sortDirection = SortType::ASC;
        return $this;
    }

    /**
     * Set sort direction in descending order
     *
     * @return CollectionQueryInterface<T>
     */
    public function desc() : CollectionQueryInterface {
        $this->sortDirection = SortType::DESC;
        return $this;
    }
}
