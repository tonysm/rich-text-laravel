<?php

namespace Tonysm\RichTextLaravel\Actions;

class FragmentByCanonicalizingAttachments
{
    public function __invoke($content, callable $next)
    {
        return $next($this->parse($content));
    }

    public function parse($content)
    {
        return $this->fragmentByMinifyingAttachments($this->fragmentByConvertingTrixAttachments($content));
    }

    private function fragmentByConvertingTrixAttachments($content)
    {
        return $content;
    }

    private function fragmentByMinifyingAttachments($content)
    {
        return $content;
    }
}
