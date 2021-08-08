<?php

namespace Tonysm\RichTextLaravel\Attachables;

interface AttachableContract
{
    public function richTextSgid(): string;

    public function richTextRender($content = null): string;
}
