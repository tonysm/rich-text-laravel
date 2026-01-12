<?php

namespace Tonysm\RichTextLaravel\Editor;

use DOMElement;
use Tonysm\RichTextLaravel\Attachment;
use Tonysm\RichTextLaravel\Fragment;

class LexxyEditor implements Editor
{
    public function asCanonical(Fragment $fragment): Fragment
    {
        return $fragment;
    }

    public function asEditable(Fragment $fragment): Fragment
    {
        return $fragment->replace(Attachment::$SELECTOR, function (DOMElement $node) {
            if (! $node->hasAttribute('url')) {
                // TODO: We also need to render the normal attachments (those that don't rely on content attribute, but actually render inside the tag)...
                $node->setAttribute('content', match (true) {
                    $node->hasAttribute('content') => json_encode($node->getAttribute('content')),
                    default => '',
                });
            }

            return $node;
        });
    }
}
