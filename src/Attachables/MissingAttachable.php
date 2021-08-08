<?php

namespace Tonysm\RichTextLaravel\Attachables;

class MissingAttachable implements AttachableContract
{
    use Attachable;

    public function richTextAsPlainText($caption = null)
    {
        return sprintf("[%s]", $caption ?: 'Missing Attachment');
    }

    public function richTextRender($content = null): string
    {
        return view('rich-text-laravel::attachables._missing_attachable')->render();
    }
}
