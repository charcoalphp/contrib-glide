<?php

declare(strict_types=1);

namespace Charcoal\Image\Glide\Providers;

use Charcoal\Image\Glide\Config\Config;
use Charcoal\Image\Glide\Config\GlideConfig;
use Charcoal\Image\Glide\Exceptions\EmptyCollectionException;
use Charcoal\Image\Glide\Exceptions\InvalidArgumentException;
use League\Glide\Responses\PsrResponseFactory;
use League\Glide\Responses\ResponseFactoryInterface;
use League\Glide\Server;
use League\Glide\ServerFactory;
use League\Glide\Signatures\SignatureFactory;
use League\Glide\Signatures\SignatureInterface;
use League\Glide\Urls\UrlBuilder;
use League\Glide\Urls\UrlBuilderFactory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Slim\Http\Response as SlimV3Response;
use Slim\Http\Stream as SlimV3Stream;

class GlideServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the contrib's services.
     *
     * @param  Container $container The service locator.
     * @return void
     */
    public function register(Container $container)
    {
        $container['glide/config'] = function (Container $container): GlideConfig {
            $data = ($container['config']['apis.glide'] ?? []);
            return GlideConfig::create($data);
        };

        $container['glide/request/signature'] = function (Container $container): ?SignatureInterface {
            $key = $container['glide/config']['signature'];
            if ($key) {
                return SignatureFactory::create($key);
            }

            return null;
        };

        $container['glide/response/factory'] = function (): ?ResponseFactoryInterface {
            // Slim v3
            if (class_exists('Slim\Http\Response')) {
                return new PsrResponseFactory(
                    new SlimV3Response(),
                    /** @param resource $stream */
                    function ($stream) {
                        // @codeCoverageIgnoreStart
                        return new SlimV3Stream($stream);
                        // @codeCoverageIgnoreEnd
                    }
                );
            }

            return null;
        };

        $container['glide/server'] = function (Container $container): Server {
            $name    = $container['glide/config']['default_server'];
            $servers = $container['glide/servers'];

            if (!isset($servers[$name])) {
                throw new InvalidArgumentException(sprintf(
                    'Expected default server configuration "%s" in "servers"',
                    $name
                ));
            }

            return $servers[$name];
        };

        /**
         * @return \Pimple\Container<string, callable<\League\Glide\Server>>
         */
        $container['glide/servers'] = function (Container $container): Container {
            $config = $container['glide/config'];

            if ($config['servers']->isEmpty()) {
                throw new EmptyCollectionException(
                    'Expected "servers" to contain at least one server configuration'
                );
            }

            $servers = new Container();

            foreach ($config['servers'] as $name => $server) {
                /** @var callable(): \League\Glide\Server $servers[$name] */
                $servers[$name] = function () use ($name, $server, $config, $container): Server {
                    return $this->createServer($name, $server, $config, $container);
                };
            }

            return $servers;
        };

        $container['glide/url-builder'] = function (Container $container): UrlBuilder {
            $name     = $container['glide/config']['default_server'];
            $builders = $container['glide/url-builders'];

            if (!isset($builders[$name])) {
                throw new InvalidArgumentException(sprintf(
                    'Expected default server configuration "%s" in "servers"',
                    $name
                ));
            }

            return $builders[$name];
        };

        /**
         * @return \Pimple\Container<string, callable<\League\Glide\Urls\UrlBuilder>>
         */
        $container['glide/url-builders'] = function (Container $container): Container {
            $config = $container['glide/config'];

            if ($config['servers']->isEmpty()) {
                throw new EmptyCollectionException(
                    'Expected "servers" to contain at least one server configuration'
                );
            }

            $builders = new Container();

            foreach ($config['servers'] as $name => $server) {
                /** @var callable(): \League\Glide\Urls\UrlBuilder $builders[$name] */
                $builders[$name] = function () use ($name, $server, $config, $container): UrlBuilder {
                    return $this->createUrlBuilder($name, $server, $config, $container);
                };
            }

            return $builders;
        };
    }

    protected function createServer(
        string $name,
        Config $server,
        GlideConfig $config,
        Container $container
    ): Server {
        $server = Config::snakeizeKeys($server->data());

        if (isset($server['base_url'])) {
            $path = parse_url($server['base_url'], PHP_URL_PATH);
            if ($path) {
                $server['base_url'] = $path;
            }
        }

        if (!isset($server['response'])) {
            $server['response'] = $container['glide/response/factory'];
        }

        return ServerFactory::create($server);
    }

    protected function createUrlBuilder(
        string $name,
        Config $server,
        GlideConfig $config,
        Container $container
    ): UrlBuilder {
        $baseUrl   = ($server['base_url'] ?? '');
        $signature = $this->resolveSignature($name, $server, $config, $container);

        if ($signature instanceof SignatureInterface) {
            return new UrlBuilder($baseUrl, $signature);
        }

        return UrlBuilderFactory::create($baseUrl, $signature);
    }

    /**
     * @return SignatureInterface|string|null
     */
    protected function resolveSignature(
        string $name,
        Config $server,
        GlideConfig $config,
        Container $container
    ) {
        $signature = $server['signature'];
        if ($signature) {
            return $signature;
        }

        $signature = $config['signature'];
        if ($signature) {
            return $container['glide/request/signature'];
        }

        return null;
    }
}
