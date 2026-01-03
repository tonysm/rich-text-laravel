<?php

namespace Tonysm\RichTextLaravel\Attachments;

use Tonysm\RichTextLaravel\TrixAttachment;

trait TrixConvertion
{
    /**
     * @deprecated Use fragmentByConvertingEditorAttachments instead.
     */
    public static function fragmentByConvertingTrixAttachments($content)
    {
        return static::fragmentByConvertingEditorAttachments($content);
    }

    /**
     * @deprecated Use Attachment::fromAttributes instead.
     */
    public static function fromTrixAttachment(TrixAttachment $attachment)
    {
        return static::fromAttributes($attachment->attributes());
    }

    /**
     * @deprecated Use TrixAttachment::fromAttributes instead.
     */
    public function toTrixAttachment($content = null): TrixAttachment
    {
        /** @psalm-suppress UndefinedThisPropertyFetch */
        if (! $content && method_exists($this->attachable, 'toEditorContent')) {
            $content = $this->attachable->toEditorContent();
        } elseif (! $content && method_exists($this->attachable, 'toTrixContent')) {
            $content = $this->attachable->toTrixContent();
        }

        /** @psalm-suppress UndefinedMethod */
        $attributes = $this->fullAttributes()->filter()->all();

        if ($content) {
            $attributes['content'] = $content;
        }

        return TrixAttachment::fromAttributes($attributes);
    }
}
