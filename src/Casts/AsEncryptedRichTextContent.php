<?php

namespace Tonysm\RichTextLaravel\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Tonysm\RichTextLaravel\Content;

class AsEncryptedRichTextContent implements CastsAttributes
{
    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, $key, $value, $attributes)
    {
        return encrypt(Content::toStorage($value));
    }

    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, $key, $value, $attributes)
    {
        return Content::fromStorage($value ? decrypt($value) : null);
    }
}
