<?php

namespace Tonysm\RichTextLaravel;

use DOMDocument;
use DOMNode;

class HtmlConversion
{
    public static function nodeToHtml(DOMNode $node): string
    {
        return $node->ownerDocument->saveHTML();
    }

    public static function fragmentForHtml(string $html): Fragment
    {
        $document = static::document($html);

        return new Fragment($document);
    }

    private static function document(?string $html = null): DOMDocument
    {
        libxml_use_internal_errors(true);
        $document = new DOMDocument();

        if ($html) {
            $document->loadHTML("<body>{$html}</body>", LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
        }

        return $document;
    }
}
