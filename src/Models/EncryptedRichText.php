<?php

namespace Tonysm\RichTextLaravel\Models;

use Tonysm\RichTextLaravel\Casts\AsEncryptedRichTextContent;

class EncryptedRichText extends RichText
{
    protected $casts = [
        'body' => AsEncryptedRichTextContent::class,
    ];
}
