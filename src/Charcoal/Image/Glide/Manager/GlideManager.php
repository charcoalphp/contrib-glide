<?php

declare(strict_types=1);

namespace Charcoal\Image\Glide\Manager;

use Charcoal\Image\Glide\Config\GlideConfig;
use League\Glide\Responses\ResponseFactoryInterface;
use League\Glide\Server;
use League\Glide\Signatures\SignatureInterface;
use League\Glide\Urls\UrlBuilder;

class GlideManager
{
    private GlideConfig $config;
    private ?string $currentServer = null;
    private ServerManager $servers;
    private UrlBuilderManager $urlBuilders;

    public function __construct(
        GlideConfig $config,
        ServerManager $servers = null,
        UrlBuilderManager $urlBuilders = null
    ) {
        $this->config = $config;
        $this->servers = ($servers ?? new ServerManager($config));
        $this->urlBuilders = ($urlBuilders ?? new UrlBuilderManager($config));
    }

    /**
     * Retrieves the current server name.
     */
    public function getCurrentServer(): string
    {
        return ($this->currentServer ?? $this->getDefaultServer());
    }

    /**
     * Retrieves the default server name.
     */
    public function getDefaultServer(): string
    {
        return $this->servers->getDefaultServer();
    }

    public function getServer(string $name = null): Server
    {
        $name ??= $this->getCurrentServer();

        return $this->servers->get($name);
    }

    /**
     * Retrieves the server configuration.
     *
     * @return array<string, mixed>
     */
    public function getServerConfig(string $name = null): array
    {
        $name ??= $this->getCurrentServer();

        return $this->servers->getConfig($name);
    }

    /**
     * Determines if a server is registered.
     */
    public function hasServer(string $name): bool
    {
        return $this->servers->has($name);
    }

    /**
     * Retrieves the HTTP response factory instance for a given server.
     */
    public function getResponseFactory(string $name = null): ?ResponseFactoryInterface
    {
        $name ??= $this->getCurrentServer();

        return $this->servers->getResponseFactory($name);
    }

    /**
     * Retrieves the HTTP response factory instance or fails.
     */
    public function getResponseFactoryOrFail(string $name = null): ResponseFactoryInterface
    {
        $name ??= $this->getCurrentServer();

        return $this->servers->getResponseFactoryOrFail($name);
    }

    public function getServerManager(): ServerManager
    {
        return $this->servers;
    }

    public function getSignatureHandler(): ?SignatureInterface
    {
        return $this->urlBuilders->getSignatureHandler();
    }

    /**
     * Retrieves a URL builder instance for a given server.
     */
    public function getUrlBuilder(string $name = null): UrlBuilder
    {
        $name ??= $this->getCurrentServer();

        return $this->urlBuilders->get($name);
    }

    public function getUrlBuilderManager(): UrlBuilderManager
    {
        return $this->urlBuilders;
    }

    /**
     * Sets the current server instance to use.
     */
    public function using(string $name): self
    {
        $this->servers->get($name);

        $this->currentServer = $name;

        return $this;
    }
}
