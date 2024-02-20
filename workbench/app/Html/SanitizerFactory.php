<?php

namespace Workbench\App\Html;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class SanitizerFactory
{
    private static $cache = [];

    public static function make($config = null): Sanitizer
    {
        return new Sanitizer(new HtmlSanitizer(static::configFor($config)));
    }

    private static function configFor($config = null): HtmlSanitizerConfig
    {
        return static::$cache[$config ?? 'default'] ??= match ($config) {
            'minimal' => static::minimalConfig(),
            default => static::defaultConfig(),
        };
    }

    private static function defaultConfig(): HtmlSanitizerConfig
    {
        return (new HtmlSanitizerConfig())
            ->allowSafeElements()
            ->allowAttribute('class', '*');
    }

    private static function minimalConfig(): HtmlSanitizerConfig
    {
        return (new HtmlSanitizerConfig())
            ->allowElement('br')
            ->allowElement('div')
            ->allowElement('p')
            ->allowElement('span')
            ->allowElement('img', ['class', 'src', 'alt'])
            ->allowElement('a', ['href'])
            ->allowElement('strong')
            ->allowElement('b')
            ->allowElement('em')
            ->allowElement('i')
            ->allowAttribute('class', '*');
    }
}
