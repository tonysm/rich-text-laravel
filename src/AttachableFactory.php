<?php

namespace Tonysm\RichTextLaravel;

use DOMElement;
use Tonysm\GlobalId\Facades\Locator;
use Tonysm\RichTextLaravel\Attachables\ContentAttachment;
use Tonysm\RichTextLaravel\Attachables\RemoteFile;
use Tonysm\RichTextLaravel\Attachables\RemoteImage;

class AttachableFactory
{
    public static function fromNode(DOMElement $node): Attachables\AttachableContract
    {
        if ($attachable = RichTextLaravel::attachableFromCustomResolver($node)) {
            return $attachable;
        }

        if ($node->hasAttribute('sgid') && $attachable = static::attachableFromSgid($node->getAttribute('sgid'))) {
            return $attachable;
        }

        if ($attachable = ContentAttachment::fromNode($node)) {
            return $attachable;
        }

        if ($attachable = RemoteImage::fromNode($node)) {
            return $attachable;
        }

        if ($attachable = RemoteFile::fromNode($node)) {
            return $attachable;
        }

        return new Attachables\MissingAttachable();
    }

    private static function attachableFromSgid(string $sgid)
    {
        return Locator::locateSigned($sgid, [
            'for' => 'rich-text-laravel',
        ]);
    }
}
