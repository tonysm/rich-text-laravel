<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tonysm\RichTextLaravel\Casts\AsRichTextContent;

class Comment extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'content' => AsRichTextContent::class,
    ];
}
