<?php

namespace Tonysm\RichTextLaravel\Attachments;

use DOMElement;
use Tonysm\RichTextLaravel\Attachment;
use Tonysm\RichTextLaravel\Fragment;

trait Minification
{
    public static function fragmentByMinifyingAttachments($content)
    {
        return Fragment::wrap($content)->replace(Attachment::$TAG_NAME, function (DOMElement $node) {
            return $node->cloneNode(deep: false);
        });
    }
}
