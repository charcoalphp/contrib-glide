<?php

declare(strict_types=1);

namespace Charcoal\Image\Glide\Tests\Unit;

/**
 * Test Service Provider Class
 */

use Charcoal\Image\Glide\Config\GlideConfig;
use Charcoal\Image\Glide\Manager\GlideManager;
use Charcoal\Image\Glide\Providers\GlideServiceProvider;
use League\Glide\Responses\ResponseFactoryInterface;
use Pimple\Container;

/** @phpstan-var \PHPUnit\Framework\TestCase $this */
test('provider registers services', function () {
    /** @psalm-var \PHPUnit\Framework\TestCase $this */

    $container = (new Container())->register(new GlideServiceProvider());

    expect($container->keys())->toContain(
        'glide/config',
        'glide/manager',
        'glide/response/factory',
    );

    expect($container['glide/config'])
        ->toBeInstanceOf(GlideConfig::class);

    expect($container['glide/manager'])
        ->toBeInstanceOf(GlideManager::class);

    expect($container['glide/response/factory'])
        ->toMatchConstraint(
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(ResponseFactoryInterface::class)
            )
        );
});
