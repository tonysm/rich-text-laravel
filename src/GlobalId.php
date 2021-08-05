<?php

namespace Tonysm\RichTextLaravel;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Queue\SerializesModels;

class GlobalId
{
    use SerializesModels;

    public function __construct(public $record)
    {
    }

    public static function findRecord(?string $sgid)
    {
        try {
            if ($sgid) {
                return unserialize(decrypt(base64_decode($sgid)))->record;
            }
        } catch (ModelNotFoundException) {
            // No need to do anything, just return null.
        }

        return null;
    }

    public function toString()
    {
        return base64_encode(encrypt(serialize($this)));
    }
}
