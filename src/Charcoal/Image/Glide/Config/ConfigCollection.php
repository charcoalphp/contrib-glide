<?php

declare(strict_types=1);

namespace Charcoal\Image\Glide\Config;

use ArrayIterator;
use Charcoal\Config\ConfigInterface;
use Charcoal\Image\Glide\Exceptions\EmptyCollectionException;
use Countable;
use IteratorAggregate;
use Traversable;

class ConfigCollection extends Config implements
    Countable,
    IteratorAggregate
{
    public function count(): int
    {
        return count($this->keys());
    }

    public function first(): ConfigInterface
    {
        $keys = $this->keys();
        if ($keys) {
            $first = reset($keys);
            return $this[$first];
        }

        throw new EmptyCollectionException();
    }

    public function isEmpty(): bool
    {
        return !$this->keys();
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($key, $value)
    {
        if (!($value instanceof ConfigInterface)) {
            $value = $this->createConfig($value);
        }

        parent::offsetSet($key, $value);
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function createConfig(array $data): ConfigInterface
    {
        return new Config($data);
    }
}
