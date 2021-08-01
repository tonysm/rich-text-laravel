<?php

namespace Tonysm\RichTextLaravel\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use Tonysm\RichTextLaravel\Casts\AsRichTextContent;

class Post extends Model
{
    protected $guarded = [];

    protected $casts = [
        'body' => AsRichTextContent::class,
    ];
}
