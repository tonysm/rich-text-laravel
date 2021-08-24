<?php

namespace Tonysm\RichTextLaravel\Attachments;

use DOMElement;
use Tonysm\RichTextLaravel\Fragment;
use Tonysm\RichTextLaravel\TrixAttachment;

trait TrixConvertion
{
    public static function fragmentByConvertingTrixAttachments($content)
    {
        return Fragment::wrap($content)->replace(TrixAttachment::$SELECTOR, function (DOMElement $node) {
            return static::fromTrixAttachment(new TrixAttachment($node));
        });
    }

    public static function fragmentByConvertingTrixContent($content)
    {
        return Fragment::wrap($content)->replace(TrixAttachment::$SELECTOR, function (DOMElement $node) {
            return static::fromTrixAttachment(new TrixAttachment($node));
        });
    }

    public static function fromTrixAttachment(TrixAttachment $attachment)
    {
        return static::fromAttributes($attachment->attributes());
    }

    public function toTrixAttachment($content = null)
    {
        /** @psalm-suppress UndefinedThisPropertyFetch */
        if (! $content && method_exists($this->attachable, 'toTrixContent')) {
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
