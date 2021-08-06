<?php

namespace Tonysm\RichTextLaravel\Attachables;

class MissingAttachable implements AttachableContract
{
    use Attachable;

    public function richTextRender(): string
    {
        return view('rich-text-laravel::attachables._missing_attachable')->render();
    }
}
