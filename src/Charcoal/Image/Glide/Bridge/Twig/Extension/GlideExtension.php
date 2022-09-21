<?php

declare(strict_types=1);

namespace Charcoal\Image\Glide\Bridge\Twig\Extension;

use Charcoal\Image\Glide\Config\Manipulations;
use Charcoal\Image\Glide\Manager\GlideManager;
use Charcoal\Image\Glide\Mixin\GlideManagerTrait;
use League\Glide\Urls\UrlBuilder;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Glide Twig Extension
 */
class GlideExtension extends AbstractExtension
{
    use GlideManagerTrait;

    public function __construct(GlideManager $glideManager)
    {
        $this->setGlideManager($glideManager);
    }

    public function getFilters()
    {
        return [
            // Usage: `{{ 'image.jpg' | glide(server='secondary', width=200, height=200,...) }}`
            new TwigFilter('glide', [ $this, 'buildRequestUrl' ], [ 'is_variadic' => true ]),
        ];
    }

    public function getFunctions()
    {
        return [
            // Usage: `{{ glide('image.jpg', preset='thumbnail',...) }}`
            new TwigFunction('glide', [ $this, 'buildRequestUrl' ], [ 'is_variadic' => true ]),
        ];
    }

    public function buildRequestUrl($path, array $options = [])
    {
        $manager = $this->getGlideManager();

        if (isset($options['server'])) {
            $name = $options['server'];
            unset($options['server']);
        } else {
            $name = $manager->getCurrentServer();
        }

        $params = Manipulations::convertToGlideParameters($options);

        return $manager->getUrlBuilder($name)->buildRequestUrl($path, $params);
    }
}
