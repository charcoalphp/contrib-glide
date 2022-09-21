<?php

declare(strict_types=1);

namespace Charcoal\Image\Glide\Providers;

use Charcoal\Image\Glide\Bridge\Twig\Extension\GlideExtension as TwigGlideExtension;
use Charcoal\Image\Glide\Config\GlideConfig;
use Charcoal\Image\Glide\Factory\ResponseFactoryDiscovery;
use Charcoal\Image\Glide\Manager\GlideManager;
use Charcoal\Image\Glide\Manager\ServerManager;
use League\Glide\Responses\ResponseFactoryInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

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

        $container['glide/manager'] = function (Container $container): GlideManager {
            return new GlideManager(
                $container['glide/config'],
                new ServerManager(
                    $container['glide/config'],
                    $container->raw('glide/response/factory')
                )
            );
        };

        $container['glide/response/factory'] = fn (): ?ResponseFactoryInterface => ResponseFactoryDiscovery::find();

        $this->registerTwigExtensions($container);
    }

    /**
     * Register the contrib's services.
     *
     * @param  Container $container The service locator.
     * @return void
     */
    public function registerTwigExtensions(Container $container)
    {
        $container['glide/twig/extension'] = function (Container $container): TwigGlideExtension {
            return new TwigGlideExtension(
                $container['glide/manager']
            );
        };

        if (!isset($container['view/twig/helpers'])) {
            /**
             * @return array<\Twig\Extension\ExtensionInterface>
             */
            $container['view/twig/helpers'] = fn (): array => [];
        }

        /**
         * @param  array<\Twig\Extension\ExtensionInterface> $helpers
         * @return array<\Twig\Extension\ExtensionInterface>
         */
        $container->extend('view/twig/helpers', function (array $helpers, Container $container): array {
            return array_merge($helpers, [
                $container['glide/twig/extension'],
            ]);
        });
    }
}
