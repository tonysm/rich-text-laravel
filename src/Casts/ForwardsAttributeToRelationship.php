<?php

namespace Tonysm\RichTextLaravel\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Tonysm\RichTextLaravel\Models\RichText;

class ForwardsAttributeToRelationship implements CastsAttributes
{
    /**
     * Prepare the given value for storage.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, $key, $value, $attributes)
    {
        if (is_string($value)) {
            $richText = $this->firstOrNewRelationship($model, $key);
            $richText->fill(['body' => $value]);
        }

        return [];
    }

    /**
     * Cast the given value.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, $key, $value, $attributes)
    {
        return $this->firstOrNewRelationship($model, $key);
    }

    /**
     * @return RichText
     */
    public function firstOrNewRelationship(Model $model, string $field)
    {
        $relationship = $model::fieldToRichTextRelationship($field);

        if ($model->{$relationship}) {
            return $model->{$relationship};
        }

        $richText = $model->{$relationship}()->make(['field' => $field]);
        $model->setRelation($relationship, $richText);

        return $richText;
    }
}
