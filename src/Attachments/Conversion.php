<?php

namespace Tonysm\RichTextLaravel\Attachments;

use Tonysm\RichTextLaravel\Fragment;
use Tonysm\RichTextLaravel\RichTextLaravel;

trait Conversion
{
    public static function fragmentByConvertingEditorAttachments($content)
    {
        return RichTextLaravel::editor()->asCanonical(Fragment::wrap($content));
    }

    public function toEditorAttachment($content = null)
    {
        /** @psalm-suppress UndefinedThisPropertyFetch */
        if (! $content && method_exists($this->attachable, 'toEditorContent')) {
            $content = $this->attachable->toEditorContent();
        } elseif (! $content && method_exists($this->attachable, 'toTrixContent')) {
            $content = $this->attachable->toTrixContent();
        }

        /** @psalm-suppress UndefinedThisPropertyFetch */
        $clonedNode = $this->node->cloneNode(true);

        if ($content) {
            $clonedNode->setAttribute('content', $content);
        }

        return new static($clonedNode, $this->attachable);
    }
}
