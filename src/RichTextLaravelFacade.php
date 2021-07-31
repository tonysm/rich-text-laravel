<?php

namespace Tonysm\RichTextLaravel;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Tonysm\RichTextLaravel\RichTextLaravel
 */
class RichTextLaravelFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'rich-text-laravel';
    }
}
