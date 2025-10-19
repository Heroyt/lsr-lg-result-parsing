<?php

/**
 * @author Tomáš Vojík <xvojik00@stud.fit.vutbr.cz>, <vojik@wboy.cz>
 */

namespace Lsr\Lg\Results\Interface\Collections;

interface CollectionQueryFilterInterface
{
    /**
     * @template Collection of CollectionInterface
     *
     * @param Collection $collection
     *
     * @return CollectionQueryFilterInterface
     */
    public function apply(CollectionInterface $collection) : CollectionQueryFilterInterface;
}
