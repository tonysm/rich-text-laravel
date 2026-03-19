<?php

namespace Tonysm\RichTextLaravel;

use DOMElement;
use Tonysm\GlobalId\Facades\Locator;
use Tonysm\RichTextLaravel\Attachables\AttachableContract;
use Tonysm\RichTextLaravel\Attachables\ContentAttachment;
use Tonysm\RichTextLaravel\Attachables\RemoteFile;
use Tonysm\RichTextLaravel\Attachables\RemoteImage;

class AttachableFactory
{
    public static function fromNode(DOMElement $node): AttachableContract
    {
        if (($attachable = RichTextLaravel::attachableFromCustomResolver($node)) instanceof AttachableContract) {
            return $attachable;
        }

        if ($node->hasAttribute('sgid') && $attachable = static::attachableFromSgid($node->getAttribute('sgid'))) {
            return $attachable;
        }

        if (($attachable = ContentAttachment::fromNode($node)) instanceof ContentAttachment) {
            return $attachable;
        }

        if (($attachable = RemoteImage::fromNode($node)) instanceof RemoteImage) {
            return $attachable;
        }

        if (($attachable = RemoteFile::fromNode($node)) instanceof RemoteFile) {
            return $attachable;
        }

        return new Attachables\MissingAttachable;
    }

    private static function attachableFromSgid(string $sgid)
    {
        return Locator::locateSigned($sgid, [
            'for' => 'rich-text-laravel',
        ]);
    }
}
