<?php

declare(strict_types=1);

namespace Charcoal\Image\Glide\Factory;

use Charcoal\Image\Glide\Exceptions\ResponseFactoryNotFoundException;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use GuzzleHttp\Psr7\Stream as GuzzleStream;
use League\Glide\Responses\PsrResponseFactory;
use League\Glide\Responses\ResponseFactoryInterface;
use League\Glide\Responses\SlimResponseFactory;
use Slim\Http\Response as SlimV3Response;
use Slim\Http\Stream as SlimV3Stream;
use Slim\Psr7\Response as SlimV4Response;
use Slim\Psr7\Stream as SlimV4Stream;

class ResponseFactoryDiscovery
{
    /**
     * A list of candidates to find classes.
     *
     * @var string[]
     */
    private static array $finders = [
        'trySlimGlideAdapter',
        'trySlimV3',
        'trySlimV4',
        'tryGuzzle',
    ];

    /**
     * The resolved finder.
     *
     * @var ?string
     */
    private static ?string $finder = null;

    /**
     * Finds an HTTP response factory.
     */
    public static function find(): ?ResponseFactoryInterface
    {
        if (self::$finder) {
            return self::{self::$finder}();
        }

        foreach (self::$finders as $finder) {
            $factory = self::{$finder}();

            if ($factory) {
                self::$finder = $finder;
                return $factory;
            }
        }

        return null;
    }

    /**
     * Finds an HTTP response factory or fail.
     *
     * @throws ResponseFactoryNotFoundException
     */
    public static function findOrFail(): ResponseFactoryInterface
    {
        $factory = static::find();

        if ($factory) {
            return $factory;
        }

        throw new ResponseFactoryNotFoundException();
    }

    /**
     * Attempts to find Guzzle V3.
     *
     * {@link https://github.com/guzzle/psr7}
     */
    protected static function tryGuzzle(): ?ResponseFactoryInterface
    {
        if (!class_exists(GuzzleResponse::class)) {
            return null;
        }

        // @codeCoverageIgnoreStart
        return new PsrResponseFactory(
            new GuzzleResponse(),
            /** @param resource $stream */
            function ($stream) {
                return new GuzzleStream($stream);
            }
        );
        // @codeCoverageIgnoreEnd
    }

    /**
     * Attempts to find the official Glide adapter for Slim.
     *
     * {@link https://github.com/thephpleague/glide-slim}
     */
    protected static function trySlimGlideAdapter(): ?ResponseFactoryInterface
    {
        if (!class_exists(SlimResponseFactory::class)) {
            return null;
        }

        // @codeCoverageIgnoreStart
        return new SlimResponseFactory();
        // @codeCoverageIgnoreEnd
    }

    /**
     * Attempts to find Slim V3.
     *
     * {@link https://github.com/slimphp/slim/tree/3.x}
     */
    protected static function trySlimV3(): ?ResponseFactoryInterface
    {
        if (!class_exists(SlimV3Response::class)) {
            return null;
        }

        // @codeCoverageIgnoreStart
        return new PsrResponseFactory(
            new SlimV3Response(),
            /** @param resource $stream */
            function ($stream) {
                return new SlimV3Stream($stream);
            }
        );
        // @codeCoverageIgnoreEnd
    }

    /**
     * Attempts to find Slim V4.
     *
     * {@link https://github.com/slimphp/slim/tree/4.x}
     */
    protected static function trySlimV4(): ?ResponseFactoryInterface
    {
        if (!class_exists(SlimV4Response::class)) {
            return null;
        }

        // @codeCoverageIgnoreStart
        return new PsrResponseFactory(
            new SlimV4Response(),
            /** @param resource $stream */
            function ($stream) {
                return new SlimV4Stream($stream);
            }
        );
        // @codeCoverageIgnoreEnd
    }
}
