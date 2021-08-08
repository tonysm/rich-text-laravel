<?php

namespace Tonysm\RichTextLaravel\Actions;

class FragmentByCanonicalizingAttachmentGalleries
{
    public function __invoke($content, callable $next)
    {
        return $next($this->parse($content));
    }

    public function parse($content)
    {
        return $content;
    }
}
