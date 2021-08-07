<?php

namespace Tonysm\RichTextLaravel;

use DOMDocument;
use DOMElement;

class Document
{
    public static function createDocument(string $contents = ""): DOMDocument
    {
        libxml_use_internal_errors(true);
        $document = new DOMDocument();
        $document->preserveWhiteSpace = true;
        $document->formatOutput = false;

        if ($contents) {
            $document->loadHTML($contents, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        }

        return $document;
    }

    public static function createElement(string $tagname, array $attributes = [], string $contents = ""): DOMElement
    {
        $element = static::createDocument($contents)->createElement($tagname, $contents);

        foreach ($attributes as $attr => $value) {
            $element->setAttribute($attr, $value);
        }

        return $element;
    }
}
