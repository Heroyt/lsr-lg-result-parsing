<?php

namespace Lsr\Lg\Results\Collections;

use Lsr\Lg\Results\Enums\Comparison;
use Lsr\Lg\Results\Interface\Collections\CollectionInterface;
use Lsr\Lg\Results\Interface\Collections\CollectionQueryFilterInterface;
use Lsr\Orm\Model;

/**
 * @template T of Model
 * @implements CollectionQueryFilterInterface<T>
 */
class CollectionCompareFilter implements CollectionQueryFilterInterface
{
    /**
     * @param  string  $property
     * @param  Comparison  $comparison
     * @param  T  $value
     */
    public function __construct(
        public string     $property,
        public Comparison $comparison,
        public mixed      $value,
    ) {}

    /**
     * @param  CollectionInterface<T>  $collection
     *
     * @return CollectionQueryFilterInterface<T>
     */
    public function apply(CollectionInterface $collection) : CollectionQueryFilterInterface {
        $remove = [];
        foreach ($collection as $key => $value) {
            if (property_exists($value, $this->property)) {
                switch ($this->comparison) {
                    case Comparison::GREATER:
                        if ($value->{$this->property} <= $this->value) {
                            $remove[] = $key;
                        }
                        break;
                    case Comparison::LESS:
                        if ($value->{$this->property} >= $this->value) {
                            $remove[] = $key;
                        }
                        break;
                    case Comparison::EQUAL:
                        if ($value->{$this->property} !== $this->value) {
                            $remove[] = $key;
                        }
                        break;
                    case Comparison::NOT_EQUAL:
                        if ($value->{$this->property} === $this->value) {
                            $remove[] = $key;
                        }
                        break;
                    case Comparison::GREATER_EQUAL:
                        if ($value->{$this->property} < $this->value) {
                            $remove[] = $key;
                        }
                        break;
                    case Comparison::LESS_EQUAL:
                        if ($value->{$this->property} > $this->value) {
                            $remove[] = $key;
                        }
                        break;
                }
            }
        }
        foreach ($remove as $key) {
            unset($collection[$key]);
        }
        return $this;
    }
}
