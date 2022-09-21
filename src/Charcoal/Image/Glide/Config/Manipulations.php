<?php

declare(strict_types=1);

namespace Charcoal\Image\Glide\Config;

use Charcoal\Image\Glide\Exception\UnknownManipulationException;

/**
 * Glide Manipulations
 *
 * Last updated: 2022-09-21
 * Supported version: 2.2.2 (2022-02-21)
 *
 * {@link https://glide.thephpleague.com/2.0/api/quick-reference/}
 */
class Manipulations
{
    /**
     * Map of available custom manipulation names to Glide query parameters.
     *
     * @var array<string, string>
     */
    public static $api = [
        // Sorted Glide manipulations
        'background'        => 'bg',
        'blur'              => 'blur',
        'border'            => 'border',
        'brightness'        => 'bri',
        'contrast'          => 'con',
        'crop'              => 'fit',
        'devicePixelRatio'  => 'dpr',
        'filter'            => 'filt',
        'fit'               => 'fit',
        'flip'              => 'flip',
        'format'            => 'fm',
        'gamma'             => 'gam',
        'height'            => 'h',
        'manualCrop'        => 'crop',
        'orientation'       => 'or',
        'pixelate'          => 'pixel',
        'quality'           => 'q',
        'sharpen'           => 'sharp',
        'watermark'         => 'mark',
        'watermarkFit'      => 'markfit',
        'watermarkHeight'   => 'markh',
        'watermarkOpacity'  => 'markalpha',
        'watermarkPaddingX' => 'markx',
        'watermarkPaddingY' => 'marky',
        'watermarkPosition' => 'markpos',
        'watermarkWidth'    => 'markw',
        'width'             => 'w',
        // Sorted Glide options
        'preset'            => 'p',
    ];

    /**
     * @param  array<(int|string), mixed> $manipulationNames
     * @return array<(int|string), mixed>
     */
    public static function convertToGlideParameters(array $manipulations): array
    {
        $glideManipulations = [];

        foreach ($manipulations as $name => $value) {
            if (is_string($name)) {
                $name = self::convertToGlideParameter($name);
                $glideManipulations[$name] = $value;
            } elseif (is_string($value)) {
                $value = self::convertToGlideParameter($value);
                $glideManipulations[] = $value;
            } else {
                throw new UnknownManipulationException(
                    sprintf('Manipulation "%s" is not invalid', $name)
                );
            }
        }

        return $glideManipulations;
    }

    /**
     * Converts the given long manipulation name to the short parameter name
     * if applicable, otherwise returns the given manipulation name.
     */
    public static function convertToGlideParameter(string $manipulationName): string
    {
        return (self::$api[$manipulationName] ?? $manipulationName);
    }

    /**
     * Converts the given long manipulation name to the short parameter name
     * if applicable, otherwise throws an exception.
     */
    public static function convertToGlideParameterOrFail(string $manipulationName): string
    {
        if (isset(self::$api[$manipulationName])) {
            return self::$api[$manipulationName];
        }

        throw new UnknownManipulationException(
            sprintf('Manipulation "%s" is not defined', $manipulationName)
        );
    }
}
