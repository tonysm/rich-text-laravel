<?php

namespace Tonysm\RichTextLaravel;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Queue\SerializesModels;

class GlobalId
{
    use SerializesModels;

    public static function findRecord(?string $sgid)
    {
        try {
            if ($sgid) {
                return unserialize(decrypt(base64_decode($sgid)))->record;
            }
        } catch (ModelNotFoundException | DecryptException) {
            // No need to do anything, just return null.
        }

        return null;
    }

    public function __construct(public $record)
    {
    }

    public function toString()
    {
        return base64_encode(encrypt(serialize($this)));
    }
}
