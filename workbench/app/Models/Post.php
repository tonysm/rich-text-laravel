<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tonysm\RichTextLaravel\Models\Traits\HasRichText;

class Post extends Model
{
    use HasFactory;
    use HasRichText;

    protected $guarded = [];

    protected $richTextAttributes = ['body'];

    public function comments()
    {
        return $this->hasMany(Comment::class)->oldest();
    }
}
