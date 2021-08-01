<?php

namespace Tonysm\RichTextLaravel\Attachables;

use DOMElement;

class MissingAttachable implements AttachableContract
{
    use Attachable;

    public static function fromNode(array $attachmentData, DOMElement $attachment): ?AttachableContract
    {
        return new static();
    }

    public function richTextRender(): string
    {
        return "☒";
    }
}
