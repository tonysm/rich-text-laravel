<?php

namespace Tonysm\RichTextLaravel\Attachables;

use DOMElement;

class MissingAttachable implements AttachableContract
{
    use Attachable;

    public static function fromNode(array $attachmentData, array $trixAttributes, DOMElement $attachment): ?AttachableContract
    {
        return new static();
    }

    public function richTextRender(): string
    {
        return view('rich-text-laravel::attachables._missing_attachable')->render();
    }
}
