<?php

namespace Tonysm\RichTextLaravel\Actions;

use DOMDocument;
use DOMXPath;
use Tonysm\RichTextLaravel\TrixContent;

class ExtractAttachables
{
    public function __invoke(string $content, callable $each)
    {
        libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $doc->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $xpath = new DOMXPath($doc);

        $attachables = $xpath->query(TrixContent::ATTACHABLE_SELECTOR);

        if ($attachables !== false) {
            /** @var \DOMElement $attachable */
            foreach ($attachables as $attachable) {
                $each($attachable, $doc);
            }
        }
    }
}
