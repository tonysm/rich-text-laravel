<?php

namespace Tonysm\RichTextLaravel;

trait Serialization
{
    public static function load($content): ?static
    {
        if ($content) {
            return new static($content);
        }

        return null;
    }

    public static function dump($content)
    {
        if ($content === null) {
            return null;
        }

        if ($content instanceof static) {
            return $content->toHtml();
        }

        return (new static($content))->toHtml();
    }
}
