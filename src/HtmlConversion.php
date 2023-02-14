<?php

namespace Tonysm\RichTextLaravel;

use DOMDocument;
use DOMElement;

class HtmlConversion
{
    public static function nodeElementToHtml(DOMElement $node): string
    {
        return $node->ownerDocument->saveHTML($node);
    }

    public static function nodeToHtml(DOMDocument $node): string
    {
        return preg_replace("#</?rich-text-root>\n*#", '', $node->saveHTML($node->documentElement));
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
            // We're using a hack here to force the document encoding properly.
            // Then, we're going to remove the encoding tag from the document
            // right before returning it so we can have a clean document.
            // @see http://php.net/manual/en/domdocument.loadhtml.php#95251

            $document->loadHTML("<?xml encoding=\"UTF-8\"><rich-text-root>{$html}</rich-text-root>", LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);

            foreach ($document->childNodes as $item) {
                if ($item->nodeType == XML_PI_NODE) {
                    $document->removeChild($item);
                }
            }

            $document->encoding = 'UTF-8';
        }

        return $document;
    }
}
