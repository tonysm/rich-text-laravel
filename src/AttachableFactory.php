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

        if ($attachable = static::attachableFromSgid($attachmentData, $attachment)) {
            return $attachable;
        }

        if ($attachable = RemoteImage::fromNode($attachmentData, $attachment)) {
            return $attachable;
        }

        return new MissingAttachable;
    }

    public static function fromAttachable(DOMElement $attachable): AttachableContract
    {
        try {
            return static::unserializeRichTextSgid($attachable->getAttribute('sgid'));
        } catch (ModelNotFoundException) {
            return new MissingAttachable;
        }
    }

    protected static function extractData(DOMElement $attachment): array
    {
        return json_decode(urldecode($attachment->getAttribute('data-trix-attachment')), true);
    }

    protected static function attachableFromSgid(array $data, DOMElement $attachment): ?AttachableContract
    {
        if ($data['sgid'] ?? false) {
            return static::unserializeRichTextSgid($data['sgid']);
        }

        return null;
    }

    public static function unserializeRichTextSgid(string $sgid): AttachableContract
    {
        return unserialize(decrypt(base64_decode($sgid)))->record;
    }

    public static function serializeToSgid($record): string
    {
        return base64_encode(encrypt(serialize(new GlobalId($record))));
    }
}
