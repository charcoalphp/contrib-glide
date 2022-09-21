<?php

declare(strict_types=1);

namespace Charcoal\Image\Glide\Manager;

use Charcoal\Image\Glide\Config\GlideConfig;
use Charcoal\Image\Glide\Exceptions\InvalidArgumentException;
use League\Glide\Responses\ResponseFactoryInterface;
use League\Glide\Server;
use League\Glide\ServerFactory;

class ServerManager extends AbstractServerManager
{
    /** @var (callable(): ?ResponseFactoryInterface)|ResponseFactoryInterface|null */
    private $responseFactory;

    /** @var array<string, Server> */
    private array $servers = [];

    /**
     * @param mixed $responseFactory
     */
    public function __construct(
        GlideConfig $config,
        $responseFactory = null
    ) {
        parent::__construct($config);

        $this->responseFactory = $responseFactory;
    }

    /**
     * Determines if a HTTP response factory can be resolved.
     */
    public function canResolveResponseFactory(): bool
    {
        return !is_null($this->responseFactory);
    }

    /**
     * Retrieves a server instance.
     */
    public function get(string $name = null): Server
    {
        $name ??= $this->getDefaultServer();

        if (!isset($this->servers[$name])) {
            $this->servers[$name] = $this->resolveServer($name);
        }

        return $this->servers[$name];
    }

    /**
     * Retrieves the HTTP response factory instance.
     */
    public function getResponseFactory(string $name = null): ?ResponseFactoryInterface
    {
        $name ??= $this->getDefaultServer();

        return $this->get($name)->getResponseFactory();
    }

    /**
     * Retrieves the HTTP response factory instance or fails.
     */
    public function getResponseFactoryOrFail(string $name = null): ResponseFactoryInterface
    {
        $name ??= $this->getDefaultServer();

        $responseFactory = $this->get($name)->getResponseFactory();

        if (is_null($responseFactory)) {
            throw new InvalidArgumentException(
                'Expected response factory to be defined'
            );
        }

        return $responseFactory;
    }

    /**
     * @param array<string, mixed> $config The server configuration.
     */
    protected function createServer(array $config): Server
    {
        return ServerFactory::create(
            $this->resolveServerConfig($config)
        );
    }

    /**
     * Resolves the HTTP response factory.
     */
    protected function resolveResponseFactory(): ?ResponseFactoryInterface
    {
        if ($this->responseFactory instanceof ResponseFactoryInterface) {
            return $this->responseFactory;
        }

        if (is_callable($this->responseFactory)) {
            return $this->responseFactory = ($this->responseFactory)();
        }

        throw new InvalidArgumentException(sprintf(
            'Expected response factory to be an instance of or a callback that resolves to %s',
            ResponseFactoryInterface::class
        ));
    }

    /**
     * Resolves the given server.
     */
    protected function resolveServer(string $name): Server
    {
        return $this->createServer(
            $this->getConfig($name)
        );
    }

    /**
     * Resolves the given server configuration for server creation.
     *
     * @param array<string, mixed> $config The server configuration.
     */
    protected function resolveServerConfig(array $config): array
    {
        if (isset($config['base_url'])) {
            $path = parse_url($config['base_url'], PHP_URL_PATH);
            if ($path) {
                $config['base_url'] = $path;
            }
        }

        if (!isset($config['response']) && $this->canResolveResponseFactory()) {
            $config['response'] = $this->resolveResponseFactory();
        }

        return $config;
    }
}
