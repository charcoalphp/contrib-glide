<?php

declare(strict_types=1);

namespace Charcoal\Image\Glide\Mixin;

use Charcoal\Image\Glide\Manager\GlideManager;

trait GlideManagerTrait
{
    private GlideManager $glideManager;

    public function getGlideManager(): GlideManager
    {
        return $this->glideManager;
    }

    public function setGlideManager(GlideManager $manager): void
    {
        $this->glideManager = $manager;
    }
}
