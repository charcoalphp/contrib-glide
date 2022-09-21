<?php

declare(strict_types=1);

namespace Charcoal\Image\Glide\Manager;

use Charcoal\Image\Glide\Config\GlideConfig;
use Charcoal\Image\Glide\Exceptions\InvalidArgumentException;
use League\Glide\Signatures\SignatureFactory;
use League\Glide\Signatures\SignatureInterface;
use League\Glide\Urls\UrlBuilder;
use League\Glide\Urls\UrlBuilderFactory;

class UrlBuilderManager extends AbstractServerManager
{
    private ?SignatureInterface $signatureHandler;

    /** @var array<string, UrlBuilder> */
    private array $builders = [];

    public function __construct(
        GlideConfig $config,
        SignatureInterface $signatureHandler = null
    ) {
        parent::__construct($config);

        $this->signatureHandler = $signatureHandler;
    }

    /**
     * Determines if a HTTP signature handler can be resolved.
     */
    public function canResolveSignatureHandler(): bool
    {
        return !is_null($this->getSignatureKey());
    }

    /**
     * Retrieves a URL builder instance.
     */
    public function get(string $name = null): UrlBuilder
    {
        $name ??= $this->getDefaultServer();

        if (!isset($this->builders[$name])) {
            $this->builders[$name] = $this->resolveUrlBuilder($name);
        }

        return $this->builders[$name];
    }

    public function getSignatureHandler(): ?SignatureInterface
    {
        if ($this->canResolveSignatureHandler()) {
            return $this->signatureHandler ??= $this->resolveSignatureHandler();
        }

        return null;
    }

    protected function createSignature(string $key): SignatureInterface
    {
        return SignatureFactory::create($key);
    }

    /**
     * @param array<string, mixed> $config The server configuration.
     */
    protected function createUrlBuilder(array $config): UrlBuilder
    {
        $config = $this->resolveUrlBuilderConfig($config);

        return new UrlBuilder(
            $config['base_url'],
            $this->getSignatureHandler()
        );
    }

    /**
     * Resolves the HTTP signature handler.
     */
    protected function resolveSignatureHandler(): SignatureInterface
    {
        $key = $this->getSignatureKey();
        if ($key) {
            return $this->createSignature($key);
        }

        throw new InvalidArgumentException(
            'Expected signature key to be defined'
        );
    }

    /**
     * Resolves the given URL builder for a given server.
     */
    protected function resolveUrlBuilder(string $name): UrlBuilder
    {
        return $this->createUrlBuilder(
            $this->getConfig($name)
        );
    }

    /**
     * Resolves the given server configuration for URL builder creation.
     *
     * @param array<string, mixed> $config The server configuration.
     */
    protected function resolveUrlBuilderConfig(array $config): array
    {
        $config['base_url'] ??= '';

        return $config;
    }
}
