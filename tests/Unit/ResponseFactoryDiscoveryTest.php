<?php

declare(strict_types=1);

namespace Charcoal\Image\Glide\Tests\Unit;

/**
 * Test HTTP Response Factory Discovery Class
 *
 * For testing support for Slim v3 (used by Charcoal), we are using
 * stubs of its classes (which are used by PHPStan and Psalm) to
 * conditionally test how this service provider should behavior.
 */

use Charcoal\Image\Glide\Exceptions\ResponseFactoryNotFoundException;
use Charcoal\Image\Glide\Factory\ResponseFactoryDiscovery;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use GuzzleHttp\Psr7\Stream as GuzzleStream;
use League\Glide\Responses\PsrResponseFactory;
use League\Glide\Responses\ResponseFactoryInterface;
use League\Glide\Responses\SlimResponseFactory;
use Slim\Http\Response as SlimV3Response;
use Slim\Http\Stream as SlimV3Stream;
use Slim\Psr7\Response as SlimV4Response;
use Slim\Psr7\Stream as SlimV4Stream;

/** @phpstan-var \PHPUnit\Framework\TestCase $this */
test('find returns null if no candidates', function () {
    /** @psalm-var \PHPUnit\Framework\TestCase $this */

    if ($message = hasAnyResponseFactories()) {
        $this->markTestSkipped("{$message} is installed");
    }

    $factory = ResponseFactoryDiscovery::find();

    expect($factory)->toBeInstanceOf(ResponseFactoryInterface::class);
});

/** @phpstan-var \PHPUnit\Framework\TestCase $this */
test('findOrFail throws exception if no candidates', function () {
    /** @psalm-var \PHPUnit\Framework\TestCase $this */

    if ($message = hasAnyResponseFactories()) {
        $this->markTestSkipped("{$message} is installed");
    }

    expect(fn () => ResponseFactoryDiscovery::findOrFail())
        ->toThrow(ResponseFactoryNotFoundException::class);
});

/** @phpstan-var \PHPUnit\Framework\TestCase $this */
test('find peer candidate', function (bool $exists) {
    /** @psalm-var \PHPUnit\Framework\TestCase $this */

    $factory = ResponseFactoryDiscovery::find();

    expect($factory)->toBeInstanceOf(ResponseFactoryInterface::class);
})->with([
    'guzzlehttp/psr7'     => fn () => loadGuzzle(),
    'league/glide-slim'   => fn () => loadSlimGlideAdapter(),
    'slim/slim (Slim v3)' => fn () => loadSlimV3(),
    'slim/psr7 (Slim v4)' => fn () => loadSlimV4(),
]);

/**
 * @return bool|string
 */
function hasAnyResponseFactories()
{
    if (class_exists(GuzzleResponse::class)) {
        return 'guzzlehttp/psr7';
    }

    if (class_exists(SlimResponseFactory::class)) {
        return 'league/glide-slim';
    }

    if (class_exists(SlimV3Response::class)) {
        return 'slim/slim (Slim v3)';
    }

    if (class_exists(SlimV4Response::class)) {
        return 'slim/psr7 (Slim v4)';
    }

    return false;
}

/**
 * Loads test doubles of Guzzle if dependency unavailable.
 *
 * @return bool TRUE if the dependency is available, FALSE if unavailable.
 */
function loadGuzzle(): bool
{
    if (class_exists(GuzzleResponse::class)) {
        return true;
    }

    require dirname(__DIR__, 2) . '/stubs/guzzle.php.stub';

    return false;
}

/**
 * Loads test doubles of league/glide-slim if dependency unavailable.
 *
 * @return bool TRUE if the dependency is available, FALSE if unavailable.
 */
function loadSlimGlideAdapter(): bool
{
    if (class_exists(SlimResponseFactory::class)) {
        return true;
    }

    require dirname(__DIR__, 2) . '/stubs/league.php.stub';

    return false;
}

/**
 * Loads test doubles of Slim V3 if dependency unavailable.
 *
 * @return bool TRUE if the dependency is available, FALSE if unavailable.
 */
function loadSlimV3(): bool
{
    if (class_exists(SlimV3Response::class)) {
        return true;
    }

    require dirname(__DIR__, 2) . '/stubs/slim-v3.php.stub';

    return false;
}

/**
 * Loads test doubles of Slim V4 if dependency unavailable.
 *
 * @return bool TRUE if the dependency is available, FALSE if unavailable.
 */
function loadSlimV4(): bool
{
    if (class_exists(SlimV4Response::class)) {
        return true;
    }

    require dirname(__DIR__, 2) . '/stubs/slim-v4.php.stub';

    return false;
}
