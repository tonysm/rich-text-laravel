<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Model;
use Tonysm\RichTextLaravel\Models\Traits\HasRichText;

class Page extends Model
{
    use HasRichText;

    protected $guarded = [];

    protected $richTextAttributes = [
        'body' => ['attribute' => true],
    ];
}
