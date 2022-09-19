<?php

declare(strict_types=1);

namespace Charcoal\Image\Glide\Config;

use Charcoal\Config\AbstractConfig;
use JsonSerializable;

class Config extends AbstractConfig
{
    /**
     * Cache of snake-cased words.
     *
     * @var array<string, string>
     */
    protected static $snakeCache = [];

    /**
     * Convert all string keys to snake case.
     */
    public static function snakeizeKeys(array $data): array
    {
        $normalized = [];

        foreach ($data as $key => $value) {
            if (is_string($key)) {
                $key = static::snakeize($key);
            }

            if (is_array($value)) {
                $value = static::snakeizeKeys($value);
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_map(function ($value) {
            if ($value instanceof self) {
                return $value->toArray();
            }

            return $value;
        }, $this->data());
    }

    /**
     * Convert a string to snake case.
     */
    protected static function snakeize(string $value): string
    {
        $key = $value;

        if (isset(static::$snakeCache[$key])) {
            return static::$snakeCache[$key];
        }

        if (!ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));
            $value = strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1_', $value));
        }

        return static::$snakeCache[$key] = $value;
    }
}
