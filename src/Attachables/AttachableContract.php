<?php

namespace Tonysm\RichTextLaravel\Attachables;

use DOMDocument;
use DOMElement;

interface AttachableContract
{
    public static function fromNode(array $attachmentData, DOMElement $attachment): ?AttachableContract;

    public function toDOMElement(DOMDocument $doc, DOMElement $attachable, bool $withContent = false): DOMElement;

    public function richTextRender(): string;

    public function toRichTextSgid(): string;
}
