<?php

declare(strict_types=1);

namespace Charcoal\Image\Glide\Action;

use Charcoal\App\Action\AbstractAction;
use Charcoal\Image\Glide\Exceptions\InvalidArgumentException;
use Charcoal\Image\Glide\Http\GlideController;
use Charcoal\Image\Glide\Manager\GlideManager;
use Pimple\Container;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Glide Action Controller
 *
 * Using a Charcoal action controller as a wrapper
 * for the Glide HTTP controller.
 *
 * Similar implementation as {@see \Charcoal\Image\Glide\Route\GlideRoute}.
 */
class GlideAction extends AbstractAction
{
    protected GlideManager $glideManager;

    /** Route placeholder from the URI path. */
    private ?string $path = null;

    public function getRouteData(): array
    {
        return [
            'path' => $this->getPath(),
        ];
    }

    /**
     * @return void
     */
    public function results()
    {
        // This method is unused
    }

    /**
     * @return ResponseInterface
     */
    public function run(
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $this->setMode('image');

        $controller = new GlideController(
            $this->glideManager,
            $this->logger
        );

        return $controller($request, $response, $this->getRouteData());
    }

    /**
     * Retrieves the `path` route placeholder.
     */
    protected function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * Sets the `path` route placeholder.
     */
    protected function setPath(?string $path): void
    {
        if (is_null($path) || $path) {
            $this->path = $path;
            return;
        }

        throw new InvalidArgumentException(
            'Expected route placeholder "path" to be null or a non-empty string'
        );
    }

    /**
     * @param  Container $container A service container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        $this->glideManager = $container['glide/manager'];
    }
}
