<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tonysm\RichTextLaravel\Models\Traits\HasRichText;

class Message extends Model
{
    use HasFactory;
    use HasRichText;

    protected $richTextAttributes = ['content'];

    protected $guarded = [];

    public function scopeOrdered(Builder $query): void
    {
        $query->latest();
    }
}
