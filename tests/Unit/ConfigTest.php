<?php

declare(strict_types=1);

namespace Charcoal\Image\Glide\Tests\Unit;

/**
 * Test Configuration Classes.
 *
 * This contrib provides custom configuration classes to alter
 * behaviour that conflicts with Glide's expected options.
 *
 * Assertions about Charcoal v3/4:
 *
 * 1. Converts first-level data keys to camel case during assignment of values.
 * 2. Excludes `null` values for first-level data keys when retrieving all data.
 * 3. Uses non-strict typing which coerces values of the wrong type into the
 *    expected scalar type declaration if possible.
 *
 * References:
 *
 * - {@link https://www.php.net/manual/en/language.types.declarations.php#language.types.declarations.strict}
 */

use Charcoal\Image\Glide\Config\Config;
use Charcoal\Image\Glide\Config\ConfigCollection;
use Charcoal\Image\Glide\Config\GlideConfig;
use Charcoal\Image\Glide\Exceptions\EmptyCollectionException;
use Charcoal\Image\Glide\Exceptions\InvalidArgumentException;
use TypeError;

test('configuration of one server', function () {
    $inputArray = [
        'signature'       => 'qwerty',
        'extra_option'    => false,
        'nullable_option' => null,
        'presets'         => [
            'invalid_preset' => null,
            'small_hero'     => [
                'w'   => 200,
                'h'   => 200,
                'fit' => 'crop',
                'nil' => null,
            ],
        ],
    ];

    $configAsCamelCaseArray = [
        'signature'     => 'qwerty',
        'defaultServer' => 'default',
        'servers'       => [
            'default'   => [
                'extraOption' => false,
                'presets'     => [
                    'invalid_preset' => null,
                    'small_hero'     => [
                        'w'   => 200,
                        'h'   => 200,
                        'fit' => 'crop',
                        'nil' => null,
                    ],
                ],
            ],
        ],
    ];

    $inputAsConfig = GlideConfig::create($inputArray);
    $configAsArray = $inputAsConfig->toArray();

    expect($configAsArray)->toMatchArray($configAsCamelCaseArray);
});

test('configuration of default server', function () {
    $inputArray = [
        'servers' => [
            'other_server' => [],
            'default'      => [
                'extra_option'    => false,
                'nullable_option' => null,
                'presets'         => [
                    'invalid_preset' => null,
                    'small_hero'     => [
                        'w'   => 200,
                        'h'   => 200,
                        'fit' => 'crop',
                        'nil' => null,
                    ],
                ],
            ],
        ],
    ];

    $configAsCamelCaseArray = [
        'defaultServer' => 'other_server',
        'servers'       => [
            'otherServer' => [],
            'default'     => [
                'extraOption' => false,
                'presets'     => [
                    'invalid_preset' => null,
                    'small_hero'     => [
                        'w'   => 200,
                        'h'   => 200,
                        'fit' => 'crop',
                        'nil' => null,
                    ],
                ],
            ],
        ],
    ];

    $inputAsConfig = GlideConfig::create($inputArray);
    $configAsArray = $inputAsConfig->toArray();

    expect($configAsArray)->toMatchArray($configAsCamelCaseArray);
});

test('juggling of data keys', function () {
    $inputArray = [
        'default_server'  => 'default',
        'extra_option'    => true,
        'nullable_option' => null,
        'servers'         => [
            'other_server' => [],
            'default'      => [
                'extra_option'    => false,
                'nullable_option' => null,
                'presets'         => [
                    'invalid_preset' => null,
                    'small_hero'     => [
                        'w'   => 200,
                        'h'   => 200,
                        'fit' => 'crop',
                        'nil' => null,
                    ],
                ],
            ],
        ],
    ];

    $configAsCamelCaseArray = [
        'defaultServer' => 'default',
        'extraOption'   => true,
        'servers'       => [
            'otherServer' => [],
            'default'     => [
                'extraOption'    => false,
                'presets'        => [
                    'invalid_preset' => null,
                    'small_hero'     => [
                        'w'   => 200,
                        'h'   => 200,
                        'fit' => 'crop',
                        'nil' => null,
                    ],
                ],
            ],
        ],
    ];

    $configAsSnakeCaseArray = [
        'default_server'  => 'default',
        'extra_option'    => true,
        'servers'         => [
            'other_server' => [],
            'default'      => [
                'extra_option'    => false,
                'presets'         => [
                    'invalid_preset' => null,
                    'small_hero'     => [
                        'w'   => 200,
                        'h'   => 200,
                        'fit' => 'crop',
                        'nil' => null,
                    ],
                ],
            ],
        ],
    ];

    $inputAsConfig = GlideConfig::create($inputArray);
    $configAsArray = $inputAsConfig->toArray();

    expect($configAsArray)->toMatchArray($configAsCamelCaseArray);

    expect(Config::snakeizeKeys($configAsArray))->toMatchArray($configAsSnakeCaseArray);
});

test('"default_server" option: initial state ("'.GlideConfig::DEFAULT_SERVER_NAME.'")', function () {
    $config = GlideConfig::create();

    expect($config['default_server'])->toEqual(GlideConfig::DEFAULT_SERVER_NAME);
});

test('"default_server" option: with valid input', function () {
    $config = GlideConfig::create();

    expect(fn () => $config['default_server'] = 'foo!42#$bar')
        ->not->toThrow(InvalidArgumentException::class);
    expect($config['default_server'])->toEqual('foo!42#$bar');
});

test('"default_server" option: with invalid input', function () {
    $config = GlideConfig::create();

    expect(fn () => $config['default_server'] = null)->toThrow(TypeError::class);
    expect(fn () => $config['default_server'] = '')->toThrow(InvalidArgumentException::class);
    expect(fn () => $config['default_server'] = 0)->toThrow(InvalidArgumentException::class);
    // [^3]: expect(fn () => $config['default_server'] = 42)->toThrow(InvalidArgumentException::class);
    // [^3]: expect(fn () => $config['default_server'] = true)->toThrow(InvalidArgumentException::class);
    expect(fn () => $config['default_server'] = [ 'foo' ])->toThrow(TypeError::class);
});

test('"servers" option: initial state (empty collection)', function () {
    $config = GlideConfig::create();

    $servers = $config['servers'];

    expect($servers)->toBeInstanceOf(ConfigCollection::class);
    expect($servers->isEmpty())->toBeTrue();
    expect(fn () => $servers->first())->toThrow(EmptyCollectionException::class);
});

test('"servers" option: with valid input', function () {
    $config = GlideConfig::create([
        'servers' => [
            'other_server' => [],
            'default'      => [
                'extra_option'    => false,
                'nullable_option' => null,
                'presets'         => [
                    'invalid_preset' => null,
                    'small_hero'     => [
                        'w'   => 200,
                        'h'   => 200,
                        'fit' => 'crop',
                        'nil' => null,
                    ],
                ],
            ],
        ],
    ]);

    $servers = $config['servers'];

    expect($servers->isEmpty())->toBeFalse();

    expect($servers->first())->toBe($servers['otherServer']);

    expect($servers)
        ->toHaveCount(2)
        ->toBeIterable()
        ->each
            ->toBeInstanceOf(Config::class);
});

test('"servers" option: with invalid input', function () {
    $config = GlideConfig::create();

    expect(fn () => $config['servers'] = 'foobar')->toThrow(InvalidArgumentException::class);
});

test('"signature" option: initial state (null)', function () {
    $config = GlideConfig::create();

    expect($config['signature'])->toBeNull();
});

test('"signature" option: with valid input', function () {
    $config = GlideConfig::create();

    expect(fn () => $config['signature'] = null)
        ->not->toThrow(InvalidArgumentException::class);
    expect($config['signature'])->toBeNull();

    expect(fn () => $config['signature'] = 'foo!42#$bar')
        ->not->toThrow(InvalidArgumentException::class);
    expect($config['signature'])->toEqual('foo!42#$bar');
});

test('"signature" option: with invalid input', function () {
    $config = GlideConfig::create();

    expect(fn () => $config['signature'] = '')->toThrow(InvalidArgumentException::class);
    expect(fn () => $config['signature'] = 0)->toThrow(InvalidArgumentException::class);
    // [^3]: expect(fn () => $config['signature'] = 42)->toThrow(InvalidArgumentException::class);
    // [^3]: expect(fn () => $config['signature'] = true)->toThrow(InvalidArgumentException::class);
    expect(fn () => $config['signature'] = [ 'foo' ])->toThrow(TypeError::class);
});
