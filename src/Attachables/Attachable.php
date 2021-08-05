<?php

namespace Tonysm\RichTextLaravel\Attachables;

use DOMDocument;
use DOMElement;
use Tonysm\RichTextLaravel\GlobalId;

trait Attachable
{
    public static function fromNode(array $data, DOMElement $attachment): AttachableContract
    {
        if ($data['sgid'] ?? false) {
            return GlobalId::findRecord($data['sgid']);
        }

        return null;
    }

    public function toDOMElement(DOMDocument $document, DOMElement $attachable, bool $withContent = false): DOMElement
    {
        if ($withContent) {
            libxml_use_internal_errors(true);
            $contentDoc = new DOMDocument();
            $contentDoc->loadHTML($content = $this->richTextRender(), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

            if ($importedNode = $document->importNode($contentDoc->documentElement, true)) {
                $attachable->appendChild($importedNode);
            }

            $attachable->setAttribute('data-trix-attachment', json_encode([
                'sgid' => $this->toRichTextSgid(),
                'content' => $content,
            ]));
        } else {
            $attachable->setAttribute('sgid', $this->toRichTextSgid());
        }

        return $attachable;
    }

    public function toRichTextSgid(): string
    {
        return (new GlobalId($this))->toString();
    }
}
