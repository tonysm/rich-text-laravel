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
                $node->setAttribute('content', match (true) {
                    $node->hasAttribute('content') => json_encode(trim($node->getAttribute('content'))),
                    default => json_encode(trim(Attachment::fromNode($node)->toHtml())),
                });
            }

            return $node;
        });
    }
}
