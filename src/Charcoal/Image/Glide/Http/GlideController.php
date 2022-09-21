<?php

declare(strict_types=1);

namespace Charcoal\Image\Glide\Http;

use Charcoal\Image\Glide\Manager\GlideManager;
use League\Glide\Filesystem\FileNotFoundException;
use League\Glide\Filesystem\FilesystemException;
use League\Glide\Signatures\SignatureException;
use Pimple\Container;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;

/**
 * Glide HTTP Controller
 *
 * Using Glide to generate and return an image.
 *
 * Route arguments:
 *
 * - `{path:.*}` - URI path to an image.
 *
 * Query parameters:
 *
 * - {@link https://glide.thephpleague.com/2.0/api/quick-reference/}
 *
 * @mixin GlideManager
 */
class GlideController implements
    LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected GlideManager $glideManager;

    public function __construct(
        GlideManager $glideManager,
        LoggerInterface $logger = null
    ) {
        $this->glideManager = $glideManager;

        if ($logger) {
            $this->setLogger($logger);
        }
    }

    /**
     * Dynamically calls the Glide Manager instance.
     *
     * @param  string $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->glideManager->{$method}(...$parameters);
    }

    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $path = $request->getUri()->getPath();

        parse_str($request->getUri()->getQuery(), $params);

        $allowedParams = ($this->getServerConfig()['allowed_params'] ?? []);
        if ($allowedParams) {
            $params = array_intersect_key($params, array_flip($allowedParams));
        }

        $context = [
            'path'  => $path,
            'query' => $params,
        ];

        try {
            $this->validateSignature($path, $params);

            $cachedPath = $this->generateImageFromPath($path, $params);
            $context['cachedPath'] = $cachedPath;

            $this->log(LogLevel::DEBUG, 'Cached', $context);

            return $this->createResponse($cachedPath);
        } catch (SignatureException $e) {
            $this->logThrown(LogLevel::NOTICE, $e, $context);
        } catch (FileNotFoundException | FilesystemException $e) {
            $this->logThrown(LogLevel::WARNING, $e, $context);

            return $this->respondWithNotFound($response);
        } catch (Throwable $t) {
            $this->logThrown(LogLevel::ERROR, $t, $context);
        }

        return $this->respondWithBadRequest($response);
    }

    /**
     * Create a HTTP response.
     *
     * @param  string $path Generated image path.
     * @return ResponseInterface
     */
    protected function createResponse(string $path): ResponseInterface
    {
        return $this->getResponseFactoryOrFail()->create(
            $this->getServer()->getCache(),
            $path
        );
    }

    /**
     * Generate a manipulated image by a path.
     *
     * @param  string $path   Requested image path.
     * @param  array  $params Requested image manipulation parameters.
     * @return string Generated image path.
     */
    protected function generateImageFromPath(string $path, array $params): string
    {
        return $this->getServer()->makeImage($path, $params);
    }

    /**
     * Sends a message to a PSR-3 logger if available.
     */
    protected function log(
        string $level,
        string $message,
        array $context = []
    ): void {
        if (!$this->logger) {
            return;
        }

        if (isset($context['path'])) {
            $message = sprintf('%s -- %s', $message, $context['path']);
        }

        $this->logger->log(
            $level,
            sprintf('[Glide] %s', $message),
            $context
        );
    }

    /**
     * Sends a message derived from the thrown object, Error or Exception,
     * to a PSR-3 logger if available.
     */
    protected function logThrown(
        string $level,
        Throwable $thrown,
        array $context = []
    ): void {
        $this->log(
            $level,
            $thrown->getMessage(),
            ($context + [ 'exception' => $thrown ])
        );
    }

    protected function respondWithBadRequest(ResponseInterface $response): ResponseInterface
    {
        $response->getBody()->write('Bad Request');

        return $response
            ->withStatus(400)
            ->withHeader('Content-Type', 'text/plain');
    }

    protected function respondWithNotFound(ResponseInterface $response): ResponseInterface
    {
        $response->getBody()->write('Not Found');

        return $response
            ->withStatus(404)
            ->withHeader('Content-Type', 'text/plain');
    }

    /**
     * Validate the HTTP request signature, if applicable.
     *
     * @param string $path   Requested image path.
     * @param array  $params Requested image manipulation parameters.
     */
    protected function validateSignature(string $path, array $params): void
    {
        $signature = $this->getSignatureHandler();
        if (!$signature) {
            return;
        }

        $signature->validateRequest($path, $params);
    }
}
