<?php

namespace Tonysm\RichTextLaravel;

use DOMElement;

trait TrixConvertion
{
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
        $content = $content ?: $this->attachable->richTextRender($content);

        $attributes = array_unique($this->fullAttributes());

        if ($content) {
            $attributes['content'] = $content;
        }

        return TrixAttachment::fromAttributes($attributes);
    }
}
