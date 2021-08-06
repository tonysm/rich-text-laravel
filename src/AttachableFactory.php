<?php

namespace Tonysm\RichTextLaravel;

use DOMElement;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tonysm\RichTextLaravel\Attachables\AttachableContract;
use Tonysm\RichTextLaravel\Attachables\MissingAttachable;
use Tonysm\RichTextLaravel\Attachables\RemoteImage;

class AttachableFactory
{
    public static function fromNode(DOMElement $node): AttachableContract
    {
        if ($node->hasAttribute('sgid') && $attachable = static::attachableFromSgid($node->getAttribute('sgid'))) {
            return $attachable;
        }

        if ($attachable = RemoteImage::fromNode($node)) {
            return $attachable;
        }

        return new MissingAttachable();
    }

    /**
     * @throws ModelNotFoundException
     */
    public static function fromAttachableSgid(string $sgid): AttachableContract
    {
        return GlobalId::fromStorage($sgid)->record;
    }

    private static function attachableFromSgid(string $sgid): ?AttachableContract
    {
        try {
            return static::fromAttachableSgid($sgid);
        } catch (ModelNotFoundException | DecryptException) {
            return null;
        }
    }
}
