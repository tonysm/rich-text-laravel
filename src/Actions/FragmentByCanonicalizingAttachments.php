<?php

namespace Tonysm\RichTextLaravel\Actions;

use Tonysm\RichTextLaravel\Attachment;
use Tonysm\RichTextLaravel\Attachments;

class FragmentByCanonicalizingAttachments
{
    use Attachments\Minification;
    use Attachments\TrixConvertion;

    public function __invoke($content, callable $next)
    {
        return $next($this->parse($content));
    }

    public function parse($content)
    {
        return $this->fragmentByMinifyingAttachments(
            $this->fragmentByConvertingTrixAttachments($content)
        );
    }

    public static function fromAttributes(array $attributes)
    {
        return Attachment::fromAttributes($attributes);
    }
}
