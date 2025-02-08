<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\Interface;

/**
 * @template T of array<string,mixed>
 */
interface WithMetaInterface
{

    /**
     * @return $this
     */
    public function setMetaValue(string $key, mixed $value) : static;

    /**
     * @return T|array<string,mixed>
     */
    public function getMeta() : array;

    /**
     * @param  T|array<string,mixed>  $meta
     * @return $this
     */
    public function setMeta(array $meta) : static;
}