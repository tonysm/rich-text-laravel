<?php

namespace Tonysm\RichTextLaravel\Models;

use Illuminate\Database\Eloquent\Model;
use Tonysm\RichTextLaravel\Casts\AsRichTextContent;

class RichText extends Model
{
    protected $table = 'rich_texts';

    protected $fillable = [
        'field',
        'body',
    ];

    protected $casts = [
        'body' => AsRichTextContent::class,
    ];
}
