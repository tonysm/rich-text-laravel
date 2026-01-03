<?php

namespace Tonysm\RichTextLaravel\Tests\Fixtures;

use DOMElement;
use Tonysm\RichTextLaravel\Attachment;
use Tonysm\RichTextLaravel\Editor\Editor;
use Tonysm\RichTextLaravel\Fragment;
use Tonysm\RichTextLaravel\HtmlConversion;

class TestEditor implements Editor
{
    public function asCanonical(Fragment $fragment): Fragment
    {
        return $fragment->replace('test-editor-attachment', function (DOMElement $node): Attachment {
            return Attachment::fromAttributes([
                'sgid' => $node->getAttribute('sgid'),
                'content-type' => $node->getAttribute('content-type'),
            ]);
        });
    }

    public function asEditable(Fragment $fragment): Fragment
    {
        return $fragment->replace(Attachment::$TAG_NAME, function (DOMElement $node) {
            $newElement = HtmlConversion::createElement('test-editor-attachment', [
                'sgid' => $node->getAttribute('sgid'),
                'content-type' => $node->getAttribute('content-type'),
            ]);

            return Fragment::wrap($newElement->ownerDocument->saveHTML($newElement));
        });
    }
}
