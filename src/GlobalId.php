<?php

namespace Tonysm\RichTextLaravel;

use Illuminate\Queue\SerializesModels;

class GlobalId
{
    use SerializesModels;

    public function __construct(public $record)
    {
    }

    public static function fromStorage(string $sgid)
    {
        return unserialize(decrypt(base64_decode($sgid)));
    }

    public function toStorage(): string
    {
        return base64_encode(encrypt(serialize($this)));
    }
}
