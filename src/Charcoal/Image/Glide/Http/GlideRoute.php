<?php

declare(strict_types=1);

namespace Charcoal\Image\Glide\Http;

use Charcoal\App\Route\RouteInterface;
use Charcoal\Image\Glide\Http\GlideController;
use Pimple\Container;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Glide Route Controller
 *
 * Using a Charcoal route controller as a wrapper
 * for the Glide HTTP controller.
 *
 * This route controller should work as a _template route_
 * and an _action route_.
 *
 * Similar implementation as {@see \Charcoal\Image\Glide\Action\GlideAction}.
 */
class GlideRoute implements
    RouteInterface
{
    /** @var array<string, mixed> */
    private $routeData = [];

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        $this->routeData = (
            $data['config']['action_data'] ??
            $data['config']['template_data'] ??
            []
        );
    }

    /**
     * Generate and return image response.
     *
     * @return ResponseInterface
     */
    public function __invoke(
        Container $container,
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $controller = new GlideController(
            $container['glide/manager'],
            $container['logger']
        );

        return $controller($request, $response, $this->routeData);
    }
}
