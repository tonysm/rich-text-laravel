<?php

namespace Tonysm\RichTextLaravel\Attachables;

use DOMDocument;
use DOMElement;

trait Attachable
{
    public function toDOMElement(DOMDocument $document, DOMElement $attachable, bool $withContent = false): DOMElement
    {
        libxml_use_internal_errors(true);
        $attachable->setAttribute('sgid', $this->toSgid());

        if ($withContent) {
            libxml_use_internal_errors(true);
            $contentDoc = new DOMDocument();
            $contentDoc->loadHTML($this->render(), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

            if ($importedNode = $document->importNode($contentDoc->documentElement, true)) {
                $attachable->appendChild($importedNode);
            }
        }

        return $attachable;
    }
}
