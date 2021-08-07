<?php

namespace Tonysm\RichTextLaravel\Actions\Rendering;

class InjectAttachmentContent
{
    public function __invoke(string $content, callable $next): string
    {
        return $next($this->parse($content));
    }

    public function parse(string $content): string
    {
        return $content;
    }
}
