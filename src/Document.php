<?php

namespace Tonysm\RichTextLaravel;

use DOMDocument;
use DOMElement;

class Document
{
    public static function createElement(string $tagname, array $attributes = [], string $contents = ""): DOMElement
    {
        $element = static::document()->createElement($tagname, $contents);

        foreach ($attributes as $attr => $value) {
            $element->setAttribute($attr, $value);
        }

        return $element;
    }

    private static function document(): DOMDocument
    {
        return new DOMDocument();
    }
}
