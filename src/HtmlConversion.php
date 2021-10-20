<?php

namespace Tonysm\RichTextLaravel;

use DOMDocument;
use DOMElement;

class HtmlConversion
{
    public static function nodeToHtml(DOMDocument $node): string
    {
        return preg_replace("#</?rich-text-root>\n*#", "", $node->saveHTML($node->documentElement));
    }

    public static function fragmentForHtml(?string $html = null): Fragment
    {
        $document = static::document($html);

        return new Fragment($document);
    }

    public static function createElement($tagName, array $attributes = []): DOMElement
    {
        $element = static::document()->createElement($tagName);

        foreach ($attributes as $attr => $value) {
            $element->setAttribute($attr, $value);
        }

        return $element;
    }

    public static function document(?string $html = null): DOMDocument
    {
        libxml_use_internal_errors(true);
        $document = new DOMDocument('1.0', 'UTF-8');

        if ($html) {
            $document->loadHTML(mb_convert_encoding("<rich-text-root>{$html}</rich-text-root>", 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
        }

        return $document;
    }
}
