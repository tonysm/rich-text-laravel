<?php

namespace Tonysm\RichTextLaravel\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class ForwardsAttributeToRelationship implements CastsAttributes
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
        if (is_string($value)) {
            $relationship = $model::fieldToRichTextRelationship($key);

            $richText = $model->{$relationship}()->firstOrNew(['field' => $key], [
                'body' => $value,
            ]);

            $model->setRelation($relationship, $richText);
        }

        return [];
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
        $relationship = $model::fieldToRichTextRelationship($key);

        return $model->{$relationship};
    }
}
