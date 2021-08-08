<?php

namespace Tonysm\RichTextLaravel;

use DOMElement;
use Tonysm\RichTextLaravel\Attachables\RemoteImage;

class AttachableFactory
{
    public static function fromNode(DOMElement $node): Attachables\AttachableContract
    {
        if ($node->hasAttribute('sgid') && $attachable = static::attachableFromSgid($node->getAttribute('sgid'))) {
            return $attachable;
        }

        if ($attachable = RemoteImage::fromNode($node)) {
            return $attachable;
        }

        return new Attachables\MissingAttachable();
    }

    private static function attachableFromSgid(string $sgid)
    {
        return GlobalId::findRecord($sgid);
    }
}
