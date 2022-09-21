<?php

// Example of Slim 3 integration

use League\Glide\Filesystem\FileNotFoundException;
use League\Glide\Signatures\SignatureFactory;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

$app->get('/images/{path:.*}', function (Request $request, Response $response, array $args) {
    try {
        $path = $request->getUri()->getPath();

        $params = $request->getParams([ 'q', 'w', 'h', 'fit', 'crop' ]);
        $this['glide/http-signature']->validateRequest($path, $params);

        $response = $this['glide/server']->getImageResponse($path, $params);
        $this['logger']->debug(
            sprintf('Glide: %s', $path)
        );

        return $response;
    } catch (FileNotFoundException $e) {
        $this['logger']->error(
            sprintf('Glide: %s', $e->getMessage())
        );

        return $response->withStatus(404)->withHeader('Content-Type', 'text/plain')->write('Not Found');
    } catch (Throwable $e) {
        $this['logger']->warning(
            sprintf('Glide: %s -- %s', $e->getMessage(), $path)
        );
    }

    return $response->withStatus(400)->withHeader('Content-Type', 'text/plain')->write('Bad Request');
});
