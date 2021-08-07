<?php

namespace Tonysm\RichTextLaravel\Exceptions;

use DOMElement;

class MalformedJsonException extends RichTextException
{
    public DOMElement $element;

    public static function failedToParseJson(DOMElement $node, string $key)
    {
        $error = new static(sprintf('Failed to parse the %s attribute', $key));
        $error->element = $node;

        return $error;
    }
}
