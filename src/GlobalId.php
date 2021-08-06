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
        return unserialize(base64_decode($sgid))->record;
    }

    public function toStorage(): string
    {
        return base64_encode(serialize($this));
    }
}
