<?php

namespace Tonysm\RichTextLaravel;

use DOMElement;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tonysm\RichTextLaravel\Attachables\AttachableContract;
use Tonysm\RichTextLaravel\Attachables\MissingAttachable;
use Tonysm\RichTextLaravel\Attachables\RemoteImage;

class AttachableFactory
{
    public static function fromNode(DOMElement $attachment): AttachableContract
    {
        $attachmentData = static::extractData($attachment);

        if ($attachable = GlobalId::findRecord($attachmentData['sgid'] ?? '')) {
            return $attachable;
        }

        if ($attachable = RemoteImage::fromNode($attachmentData, $attachment)) {
            return $attachable;
        }

        return new MissingAttachable();
    }

    public static function fromAttachable(DOMElement $attachable): AttachableContract
    {
        try {
            return GlobalId::findRecord($attachable->getAttribute('sgid'));
        } catch (ModelNotFoundException) {
            return new MissingAttachable();
        }
    }

    protected static function extractData(DOMElement $attachment): array
    {
        return json_decode(urldecode($attachment->getAttribute('data-trix-attachment')), true);
    }
}
