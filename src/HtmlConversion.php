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
        $document = static::document();

        $fragment = $document->createDocumentFragment();
        $fragment->appendXML($html);

        $document->appendChild($fragment);

        return new Fragment($fragment);
    }

    private static function document(?string $html = null): DOMDocument
    {
        libxml_use_internal_errors(true);
        $document = new DOMDocument();

        if ($html) {
            $document->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        }

        return $document;
    }
}
