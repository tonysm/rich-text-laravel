<?php

namespace Tonysm\RichTextLaravel\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RuntimeException;
use Tonysm\RichTextLaravel\Casts\ForwardsAttributeToRelationship;
use Tonysm\RichTextLaravel\Models\RichText;

trait HasRichText
{
    protected static function bootHasRichText()
    {
        $fields = (new static)->getRichTextFields();

        foreach ($fields as $field) {
            static::registerRichTextRelationships($field);
        }

        static::saved(function (Model $model) {
            foreach ($model->getRichTextFields() as $field) {
                $relationship = static::fieldToRichTextRelationship($field);

                if ($model->relationLoaded($relationship) && $model->{$relationship}->isDirty()) {
                    $model->{$relationship}->record()->associate($model);
                    $model->{$relationship}->save();
                }
            }
        });
    }

    protected static function registerRichTextRelationships(string $field): void
    {
        static::resolveRelationUsing(static::fieldToRichTextRelationship($field), function (Model $model) use ($field) {
            return $model->morphOne(RichText::class, 'record')->where('field', $field);
        });
    }

    protected function initializeHasRichText()
    {
        foreach ($this->getRichTextFields() as $field) {
            $this->mergeCasts([
                $field => ForwardsAttributeToRelationship::class,
            ]);
        }
    }

    protected function getRichTextFields(): array
    {
        if (! property_exists($this, 'richTextFields')) {
            throw new RuntimeException(sprintf('Missing protected property $richTextFields in %s model.', static::class));
        }

        return Arr::wrap($this->richTextFields);
    }

    public static function fieldToRichTextRelationship(string $field): string
    {
        return 'richText' . Str::studly($field);
    }
}
