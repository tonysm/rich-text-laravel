<?php

namespace Tonysm\RichTextLaravel\Attachables;

class MissingAttachable implements AttachableContract
{
    use Attachable;

    public function richTextAsPlainText($caption = null): string
    {
        return sprintf("[%s]", $caption ?: 'Missing Attachment');
    }

    public function richTextRender(array $options = []): string
    {
        return view('rich-text-laravel::attachables._missing_attachable', $options)->render();
    }
}
