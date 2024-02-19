<?php

use Workbench\App\Html\SanitizerFactory;

if (! function_exists('clean')) {
    function clean(string $html, string $element = 'body', $config = null)
    {
        return SanitizerFactory::make($config)->sanitize($html, $element);
    }
}
