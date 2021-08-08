<?php

namespace Tonysm\RichTextLaravel;

use DOMNode;
use Tonysm\RichTextLaravel\Attachables\RemoteImage;

class AttachableFactory
{
    public static function fromNode(DOMNode $node): Attachables\AttachableContract
    {
        if ($attachable = RemoteImage::fromNode($node)) {
            return $attachable;
        }

        return new Attachables\MissingAttachable();
    }
}
