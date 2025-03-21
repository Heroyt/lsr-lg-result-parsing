<?php

/**
 * @author Tomáš Vojík <xvojik00@stud.fit.vutbr.cz>, <vojik@wboy.cz>
 */

namespace Lsr\Lg\Results\Collections;

use Lsr\Lg\Results\Interface\Collections\CollectionInterface;
use Lsr\Lg\Results\Interface\Collections\CollectionQueryFilterInterface;
use Lsr\Orm\Model;

/**
 * @template T of Model
 * @implements CollectionQueryFilterInterface<T>
 */
class CollectionQueryFilter implements CollectionQueryFilterInterface
{
    /**
     * @param  string  $name
     * @param  T[]  $values
     * @param  bool  $method
     */
    public function __construct(
        public string $name,
        public array  $values = [],
        public bool   $method = false
    ) {}

    public function apply(CollectionInterface $collection) : CollectionQueryFilterInterface {
        $remove = [];
        foreach ($collection as $key => $model) {
            $modelValues = $this->method ? $model->{$this->name}() : $model->{$this->name};
            $filter = false;
            if (is_array($modelValues)) {
                // TODO: Compare arrays
            }
            else {
                $filter = in_array($modelValues, $this->values, false);
            }
            if (!$filter) {
                $remove[] = $key;
            }
        }
        foreach ($remove as $key) {
            unset($collection[$key]);
        }
        return $this;
    }
}
