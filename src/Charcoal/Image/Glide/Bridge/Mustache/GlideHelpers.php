<?php

declare(strict_types=1);

namespace Charcoal\Image\Glide\Bridge\Mustache;

use Charcoal\Image\Glide\Config\Manipulations;
use Charcoal\Image\Glide\Manager\GlideManager;
use Charcoal\Image\Glide\Mixin\GlideManagerTrait;
use Charcoal\View\Mustache\HelpersInterface;
use League\Glide\Urls\UrlBuilder;
use Mustache_LambdaHelper as LambdaHelper;
use LogicException;

/**
 * Glide Mustache Helpers
 *
 * Usage:
 *
 * ```mustache
 * <img src="{{# glide.hero }}image.jpg{{/ glide.hero }}" />
 * <img src="{{# glide.w=600 }}image.jpg{{/ glide.w=600 }}" />
 * ```
 *
 * ```mustache
 * {{% FILTERS }}
 * <img src="{{ image.src | glide.w=600 }}" />
 * ```
 */
class GlideHelpers implements HelpersInterface
{
    use GlideManagerTrait;

    /**
     * Store the given Glide parameters to use (Mustache tag node).
     */
    private array $options = [];

    public function __construct(GlideManager $glideManager)
    {
        $this->setGlideManager($glideManager);
    }

    /**
     * Magic: Render the Mustache section.
     *
     * @param  string            $text   The image path.
     * @param  LambdaHelper|null $helper For rendering strings in the current context.
     * @return string
     */
    public function __invoke($text, LambdaHelper $helper = null)
    {
        $url = ($helper ? $helper->render($text) : $text);

        if ($this->getGlideManager()) {
            $url = $this->buildRequestUrl($url, $this->options);

            $this->reset();
        }

        return $url;
    }

    /**
     * Magic: Determine if a property is set and is not NULL.
     *
     * Required by Mustache.
     *
     * @param  string $macro A preset or query string.
     * @return bool
     */
    public function __isset($macro)
    {
        return boolval($macro);
    }

    /**
     * Magic: Process preset and parameters.
     *
     * Required by Mustache.
     *
     * @param  string $macro A preset or query string.
     * @throws LogicException If the macro is unresolved.
     * @return mixed
     */
    public function __get($macro)
    {
        if (!$this->getGlideManager()) {
            return $this;
        }

        if (strpos($macro, '=') > 0) {
            parse_str($macro, $this->options);
            return $this;
        } else {
            $this->options['preset'] = $macro;
            return $this;
        }

        throw new LogicException(sprintf('Unknown Glide macro: %s', $macro));
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

        return $manager->getUrlBuilder($name)->buildUrl($path, $params);
    }

    /**
     * Retrieve the helpers.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'glide' => $this,
        ];
    }

    /**
     * Clear macros.
     *
     * @return void
     */
    protected function reset()
    {
        $this->options = [];
    }
}
