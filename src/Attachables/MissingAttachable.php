<?php

namespace Tonysm\RichTextLaravel\Attachables;

class MissingAttachable implements AttachableContract
{
    public function toRichTextAttributes(array $attributes): array
    {
        return $attributes;
    }

    public function richTextAsPlainText($caption = null): string
    {
        return sprintf('[%s]', $caption ?: 'Missing Attachment');
    }

    public function equalsToAttachable(AttachableContract $attachable): bool
    {
        return false;
    }

    public function richTextRender(array $options = []): string
    {
        return view('rich-text-laravel::attachables._missing_attachable', $options)->render();
    }
}
