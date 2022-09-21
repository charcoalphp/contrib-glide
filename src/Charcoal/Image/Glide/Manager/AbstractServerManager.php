<?php

declare(strict_types=1);

namespace Charcoal\Image\Glide\Manager;

use Charcoal\Image\Glide\Config\Config;
use Charcoal\Image\Glide\Config\GlideConfig;
use Charcoal\Image\Glide\Exceptions\InvalidArgumentException;

abstract class AbstractServerManager
{
    protected GlideConfig $config;

    public function __construct(
        GlideConfig $config
    ) {
        $this->config = $config;
    }

    /**
     * Determines if a server is registered.
     */
    public function has(string $name): bool
    {
        return !is_null($this->config['servers'][$name]);
    }

    /**
     * Retrieves the server configuration.
     *
     * @return array<string, mixed>
     */
    public function getConfig(string $name): array
    {
        $config = $this->config['servers'][$name];

        if (!$config) {
            throw new InvalidArgumentException(sprintf(
                'Server [%s] is not configured',
                $name
            ));
        }

        return Config::snakeizeKeys($config->toArray());
    }

    /**
     * Retrieves the default server name.
     */
    public function getDefaultServer(): string
    {
        return $this->config['default_server'];
    }

    public function getSignatureKey(): ?string
    {
        return $this->config['signature'];
    }
}
