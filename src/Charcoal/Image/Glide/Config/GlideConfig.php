<?php

declare(strict_types=1);

namespace Charcoal\Image\Glide\Config;

use Charcoal\Config\ConfigInterface;
use Charcoal\Image\Glide\Exceptions\InvalidArgumentException;

class GlideConfig extends Config
{
    public const DEFAULT_SERVER_NAME = 'default';

    private string $defaultServer = self::DEFAULT_SERVER_NAME;
    private ConfigCollection $servers;
    private ?string $signature = null;

    /**
     * @param  array<string, mixed> $data
     * @return static
     */
    public static function create(array $data = []): self
    {
        if ($data) {
            if (!array_key_exists('servers', $data)) {
                return new static(static::normalizeServerConfig($data));
            }

            if (!array_key_exists('default_server', $data)) {
                return new static(static::normalizeDefaultServerConfig($data));
            }
        }

        return new static($data);
    }

    /**
     * Sets the first defined server as the default when the user
     * has not explicitly set one.
     *
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function normalizeDefaultServerConfig(array $data): array
    {
        $names = is_array($data['servers'])
            ? array_keys($data['servers'])
            : [];

        if ($names) {
            $data['default_server'] = reset($names);
        }

        return $data;
    }

    /**
     * Normalizes Glide server configuration values.
     *
     * Allows users to define a Glide server on the first level, when there is
     * only a single server being used. Most cases will only involve a single
     * Glide server anyways.
     *
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function normalizeServerConfig(array $data): array
    {
        $server = [];
        foreach ($data as $key => $value) {
            if (in_array($key, [ 'default_server', 'servers', 'signature' ])) {
                continue;
            }

            $server[$key] = $data[$key];
            unset($data[$key]);
        }

        $data['default_server'] = (string) ($data['default_server'] ?? static::DEFAULT_SERVER_NAME);
        $data['servers'] = [
            $data['default_server'] => $server,
        ];

        return $data;
    }

    protected function getDefaultServer(): string
    {
        return $this->defaultServer;
    }

    protected function getServers(): ConfigCollection
    {
        return $this->servers ??= new ConfigCollection();
    }

    protected function getSignature(): ?string
    {
        return $this->signature;
    }

    protected function setDefaultServer(string $defaultServer): void
    {
        if ($defaultServer) {
            $this->defaultServer = $defaultServer;
            return;
        }

        throw new InvalidArgumentException(
            'Expected "default_server" to be a non-empty string'
        );
    }

    /**
     * @param ConfigCollection|array<string, (ConfigInterface|array)> $servers
     *     Collection of server configurations.
     *
     * @psalm-param mixed $servers
     */
    protected function setServers($servers): void
    {
        if (is_array($servers)) {
            $servers = new ConfigCollection($servers);
        }

        if ($servers instanceof ConfigCollection) {
            $this->servers = $servers;
            return;
        }

        throw new InvalidArgumentException(
            'Expected "servers" to be a collection of options'
        );
    }

    protected function setSignature(?string $signature): void
    {
        if (is_null($signature) || $signature) {
            $this->signature = $signature;
            return;
        }

        throw new InvalidArgumentException(
            'Expected "signature" to be null or a non-empty string'
        );
    }
}
