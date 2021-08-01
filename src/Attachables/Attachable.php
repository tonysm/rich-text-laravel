<?php

namespace Tonysm\RichTextLaravel\Attachables;

use DOMDocument;
use DOMElement;
use Tonysm\RichTextLaravel\AttachableFactory;

trait Attachable
{
    public static function fromNode(array $data, DOMElement $attachment): AttachableContract
    {
        if ($data['sgid'] ?? false) {
            return static::unserializeRichTextSgid($data['sgid']);
        }

        return null;
    }

    public function toDOMElement(DOMDocument $document, DOMElement $attachable, bool $withContent = false): DOMElement
    {
        libxml_use_internal_errors(true);
        $attachable->setAttribute('sgid', $sgid = $this->toRichTextSgid());

        $trixAttachment = [
            'sgid' => $sgid,
        ];

        if ($withContent) {
            libxml_use_internal_errors(true);
            $contentDoc = new DOMDocument();
            $contentDoc->loadHTML($content = $this->richTextRender(), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

            if ($importedNode = $document->importNode($contentDoc->documentElement, true)) {
                $attachable->appendChild($importedNode);
            }

            $trixAttachment['content'] = $content;
        }

        $attachable->setAttribute('data-trix-attachment', json_encode($trixAttachment));

        return $attachable;
    }

    public function toRichTextSgid(): string
    {
        return AttachableFactory::serializeToSgid($this);
    }
}
