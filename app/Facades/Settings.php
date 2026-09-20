<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed get(string $key, mixed $default = null)
 * @method static void set(string|array $key, mixed $value = null)
 * @method static void forget(string $key)
 * @method static void flush()
 * @method static array all()
 * @method static array visible()
 * @method static bool hasSecret(string $key)
 * @method static bool isSecret(string $key)
 * @method static array secretKeys()
 * @method static string color()
 * @method static bool sectionEnabled(string $section)
 * @method static bool recaptchaReady()
 *
 * @see \App\Support\Settings
 */
class Settings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Support\Settings::class;
    }
}
