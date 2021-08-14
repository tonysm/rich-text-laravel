<?php

namespace Tonysm\RichTextLaravel\Tests\Stubs\HasRichText;

use Illuminate\Database\Eloquent\Model;
use Tonysm\RichTextLaravel\Models\Traits\HasRichText;

class Post extends Model
{
    use HasRichText;

    protected $guarded = [];

    protected $richTextFields = [
        'body',
        'notes',
    ];
}
