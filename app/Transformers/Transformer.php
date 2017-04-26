<?php

namespace Transformers;

abstract class Transformer
{
    /**
     * transform colection
     *
     * @param array $items
     * @return array
     */
    public function transformCollection(array $items)
    {
        return array_map([$this, 'transform'], $items);
    }

    /**
     * transform item
     *
     * @param array $item
     * @return array
     */
    abstract public function transform($item);
}
