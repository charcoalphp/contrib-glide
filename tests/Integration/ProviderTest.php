<?php

declare(strict_types=1);

namespace Charcoal\Image\Glide\Tests\Integration;

/**
 * Test Service Provider Classes
 */

use Charcoal\Config\GenericConfig as Config;
use Charcoal\Image\Glide\Config\ConfigCollection;
use Charcoal\Image\Glide\Config\GlideConfig;
use Charcoal\Image\Glide\Exceptions\EmptyCollectionException;
use Charcoal\Image\Glide\Exceptions\InvalidArgumentException;
use Charcoal\Image\Glide\Providers\GlideServiceProvider;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use League\Glide\Responses\PsrResponseFactory;
use League\Glide\Responses\ResponseFactoryInterface;
use League\Glide\Server;
use League\Glide\Signatures\SignatureInterface;
use League\Glide\Urls\UrlBuilder;
use Pimple\Container;
use TypeError;

/**
 * Testing Service Provider.
 */
test('provider registers services', function () {
    $container = registerGlideServiceProvider(
        createContainerWithGlideConfig()
    );

    expect($container->keys())->toContain(
        'glide/config',
        'glide/request/signature',
        'glide/response/factory',
        'glide/server',
        'glide/servers',
        'glide/url-builder',
        'glide/url-builders'
    );
});

/**
 * Testing HTTP Request Signature service.
 */
test('"glide/request/signature" service: initial state (null)', function () {
    $container = registerGlideServiceProvider(
        createContainerWithAppConfig()
    );

    expect($container['glide/request/signature'])->toBeNull();
});

test('"glide/request/signature" service: with a signature key', function () {
    $container = registerGlideServiceProvider(
        createContainerWithGlideConfig([
            'signature' => 'example',
        ])
    );

    $signature = $container['glide/request/signature'];

    expect($signature)
        ->toBeInstanceOf(SignatureInterface::class)
        ->and($signature->generateSignature('image.jpg', [ 'w' => '100' ]))
            ->toEqual('9978a40f1fc75fa64ac92ea9baf16ff3');
});

/**
 * Testing HTTP Response Factory service.
 *
 * For testing support for Slim v3 (used by Charcoal), we are using
 * stubs of its classes (which are used by PHPStan and Psalm) to
 * conditionally test how this service provider should behavior.
 */
test('"glide/response/factory" service: initial state (without Slim v3)', function () {
    $container = registerGlideServiceProvider(
        createContainerWithAppConfig()
    );

    expect($container['glide/response/factory'])->toBeNull();
})->skip(fn () => class_exists('Slim\Http\Response'), 'Slim is available');

test('"glide/response/factory" service: initial state (with Slim v3)', function () {
    if (!class_exists('Slim\Http\Response')) {
        require dirname(__DIR__, 2) . '/stubs/slim.php.stub';
    }

    $container = registerGlideServiceProvider(
        createContainerWithAppConfig()
    );

    expect($container['glide/response/factory'])
        ->toBeInstanceOf(ResponseFactoryInterface::class);
});

/**
 * Testing Glide Server services.
 *
 * Similar to tests for Glide URL Builder services.
 */
test('"glide/servers" service: initial state (empty collection)', function () {
    $container = registerGlideServiceProvider(
        createContainerWithAppConfig()
    );

    expect(fn () => $container['glide/servers'])
        ->toThrow(EmptyCollectionException::class);
});

test('"glide/servers" service: with server definitions', function () {
    $container = registerGlideServiceProvider(
        createContainerWithGlideConfig()
    );

    $servers = $container['glide/servers'];

    expect($servers->keys())
        ->toHaveCount(count(getServersForGlideConfig()))
        ->toContain(
            'primary',
            'secondary'
        );

    /**
     * Since "glide/servers" uses a Pimple Container,
     * we can not use Pest's higher order expectations.
     */
    expect($servers['primary'])->toBeInstanceOf(Server::class);
    expect($servers['secondary'])->toBeInstanceOf(Server::class);
});

test('"glide/server" service: initial state (empty collection)', function () {
    $container = registerGlideServiceProvider(
        createContainerWithAppConfig()
    );

    expect(fn () => $container['glide/server'])
        ->toThrow(EmptyCollectionException::class);
});

test('"glide/server" service: with known server definitions', function () {
    $container = registerGlideServiceProvider(
        createContainerWithGlideConfig()
    );

    $servers    = $container['glide/servers'];
    $serverName = getGlideConfig()['default_server'];

    expect($container['glide/server'])
        ->toBeInstanceOf(Server::class)
        ->toBe($servers[$serverName]);
});

test('"glide/server" service: with unknown server', function () {
    $container = registerGlideServiceProvider(
        createContainerWithGlideConfig([
            'default_server' => 'bogus',
            'servers'        => getServersForGlideConfig(),
        ])
    );

    expect(fn () => $container['glide/server'])
        ->toThrow(InvalidArgumentException::class);
});

/**
 * Testing Glide URL Builder services.
 *
 * Similar to tests for Glide Server services.
 */
test('"glide/url-builders" service: initial state (empty collection)', function () {
    $container = registerGlideServiceProvider(
        createContainerWithAppConfig()
    );

    expect(fn () => $container['glide/url-builders'])
        ->toThrow(EmptyCollectionException::class);
});

test('"glide/url-builders" service: with server definitions', function () {
    $container = registerGlideServiceProvider(
        createContainerWithGlideConfig()
    );

    $builders = $container['glide/url-builders'];

    expect($builders->keys())
        ->toHaveCount(count(getServersForGlideConfig()))
        ->toContain(
            'primary',
            'secondary'
        );

    /**
     * Since "glide/url-builders" uses a Pimple Container,
     * we can not use Pest's higher order expectations.
     */
    expect($builders['primary'])->toBeInstanceOf(UrlBuilder::class);
    expect($builders['secondary'])->toBeInstanceOf(UrlBuilder::class);
});

test('"glide/url-builder" service: initial state (empty collection)', function () {
    $container = registerGlideServiceProvider(
        createContainerWithAppConfig()
    );

    expect(fn () => $container['glide/url-builder'])
        ->toThrow(EmptyCollectionException::class);
});

test('"glide/url-builder" service: with known server definitions', function () {
    $container = registerGlideServiceProvider(
        createContainerWithGlideConfig()
    );

    $builders   = $container['glide/url-builders'];
    $serverName = getGlideConfig()['default_server'];

    expect($container['glide/url-builder'])
        ->toBeInstanceOf(UrlBuilder::class)
        ->toBe($builders[$serverName]);
});

test('"glide/url-builder" service: with unknown server', function () {
    $container = registerGlideServiceProvider(
        createContainerWithGlideConfig([
            'default_server' => 'bogus',
            'servers'        => getServersForGlideConfig(),
        ])
    );

    expect(fn () => $container['glide/url-builder'])
        ->toThrow(InvalidArgumentException::class);
});

test('"glide/url-builder" and "glide/request/signature" services: without any signature key', function () {
    $container = registerGlideServiceProvider(
        createContainerWithGlideConfig([
            'source' => sys_get_temp_dir().'/server/foo/source',
            'cache'  => sys_get_temp_dir().'/server/foo/cache',
        ])
    );

    $builder = $container['glide/url-builder'];

    expect($builder)->toBeInstanceOf(UrlBuilder::class);

    expect($builder->getUrl('image.jpg', [ 'w' => '100' ]))
        ->toEqual('/image.jpg?w=100');
});

test('"glide/url-builder" and "glide/request/signature" services: with a signature key', function () {
    $container = registerGlideServiceProvider(
        createContainerWithGlideConfig([
            'signature' => 'example-sign-key',
            'source'    => sys_get_temp_dir().'/server/foo/source',
            'cache'     => sys_get_temp_dir().'/server/foo/cache',
        ])
    );

    $builder = $container['glide/url-builder'];

    expect($builder)->toBeInstanceOf(UrlBuilder::class);

    expect($builder->getUrl('image.jpg', [ 'w' => '100' ]))
        ->toEqual('/image.jpg?w=100&s=e1b69d4b79ecf33283128819fd008906');
});

function createGuzzleResponseFactory(): ResponseFactoryInterface
{
    return new PsrResponseFactory(
        new Response(),
        /** @param resource $stream */
        fn ($stream) => new Stream($stream)
    );
}

/**
 * @param array<string, mixed> $options
 */
function createContainerWithAppConfig(array $options = []): Container
{
    return new Container([
        'config' => new Config($options),
    ]);
}

/**
 * @param array<string, mixed>|null $options
 */
function createContainerWithGlideConfig(array $options = null): Container
{
    return createContainerWithAppConfig([
        'apis' => [
            'glide' => $options ?? getGlideConfig(),
        ],
    ]);
}

/**
 * @return array<string, mixed>
 */
function getGlideConfig(): array
{
    $servers = getServersForGlideConfig();

    return [
        'default_server' => array_key_first($servers),
        'servers'        => $servers,
        'signature'      => 'qwerty',
    ];
}

/**
 * @return array<string, array<string, mixed>>
 */
function getServersForGlideConfig(): array
{
    return [
        'primary'    => [
            'source'         => sys_get_temp_dir().'/server/primary/source',
            'cache'          => sys_get_temp_dir().'/server/primary/cache',
            'base_url'       => '/img/',
            'max_image_size' => (2000*2000),
            'presets'        => [
                'invalid_preset' => null,
                'small_hero'     => [
                    'w'   => 200,
                    'h'   => 200,
                    'fit' => 'crop',
                    'nil' => null,
                ],
            ],
            'response'       => createGuzzleResponseFactory(),
            'signature'      => 'asdfgh',
        ],
        'secondary'  => [
            'source'         => sys_get_temp_dir().'/server/secondary/source',
            'cache'          => sys_get_temp_dir().'/server/secondary/cache',
        ],
    ];
}

function registerGlideServiceProvider(Container $container): Container
{
    return $container->register(new GlideServiceProvider());
}
