<?php

namespace Tonysm\RichTextLaravel\Actions;

class RenderAttachments
{
    public function __invoke(string $content): string
    {
        return $content;
    }
}
