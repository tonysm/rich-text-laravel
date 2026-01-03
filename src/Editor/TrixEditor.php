<?php

namespace Tonysm\RichTextLaravel\Editor;

use DOMElement;
use Tonysm\RichTextLaravel\Attachment;
use Tonysm\RichTextLaravel\Fragment;
use Tonysm\RichTextLaravel\TrixAttachment;

class TrixEditor implements Editor
{
    public function asCanonical(Fragment $fragment): Fragment
    {
        return $fragment->replace(TrixAttachment::$SELECTOR, fn(DOMElement $node): ?Attachment => $this->fromTrixAttachment($node));
    }

    public function asEditable(Fragment $fragment): Fragment
    {
        return $fragment->replace(Attachment::$SELECTOR, fn(DOMElement $node): TrixAttachment => $this->toTrixAttachment($node));
    }

    private function fromTrixAttachment(DOMElement $node): ?Attachment
    {
        $trixAttachment = new TrixAttachment($node);

        return Attachment::fromAttributes($trixAttachment->attributes());
    }

    private function toTrixAttachment(DOMElement $node): TrixAttachment
    {
        $attachment = Attachment::fromNode($node);

        return $attachment->toTrixAttachment();
    }
}
