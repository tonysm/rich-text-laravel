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

    public function record()
    {
        return $this->morphTo();
    }

    public function __call($method, $arguments)
    {
        if (method_exists($this->body, $method)) {
            return call_user_func([$this->body, $method], ...$arguments);
        }

        return parent::__call($method, $arguments);
    }

    public function __toString(): string
    {
        return $this->body->render();
    }
}
