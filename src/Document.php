<?php

namespace Tonysm\RichTextLaravel;

use DOMDocument;

class Document
{
    public static function createFromContent(string $content): DOMDocument
    {
        libxml_use_internal_errors(true);
        $document = new DOMDocument();
        $document->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        return $document;
    }
}
