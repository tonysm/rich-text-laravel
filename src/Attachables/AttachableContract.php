<?php

namespace Tonysm\RichTextLaravel\Attachables;

interface AttachableContract
{
    public function richTextRender($content = null): string;
}
