<?php

namespace Tonysm\RichTextLaravel\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Tonysm\RichTextLaravel\Casts\ForwardsAttributeToRelationship;
use Tonysm\RichTextLaravel\Exceptions\RichTextException;

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

                if ($model->relationLoaded($relationship) && $model->{$field}->isDirty()) {
                    $model->{$field}->record()->associate($model);
                    $model->{$field}->save();
                }
            }
        });
    }

    public function unsetRichTextRelationshipsForLivewireDehydration()
    {
        $relationships = array_map(fn ($field) => static::fieldToRichTextRelationship($field), $this->getRichTextFields());

        foreach ($relationships as $relationship) {
            if ($this->relationLoaded($relationship)) {
                $this->unsetRelation($relationship);
            }
        }
    }

    protected static function registerRichTextRelationships(string $field): void
    {
        static::resolveRelationUsing(static::fieldToRichTextRelationship($field), function (Model $model) use ($field) {
            return $model->morphOne(config('rich-text-laravel.model'), 'record')->where('field', $field);
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
            throw RichTextException::missingRichTextFieldsProperty(static::class);
        }

        return Arr::wrap($this->richTextFields);
    }

    public static function fieldToRichTextRelationship(string $field): string
    {
        return 'richText' . Str::studly($field);
    }

    public function scopeWithRichText(Builder $query, $fields = []): void
    {
        $allFields = (new static)->getRichTextFields();

        $fields = Arr::wrap($fields);
        $fields = empty($fields) ? $allFields : $fields;

        // We're converting the attributes to the relationship pattern and
        // only then we'll perform the eager loading. If any of the given
        // fields is not a valid one, we'll throw an exception and halt.

        $fields = collect($fields)
            ->each(fn ($field) => throw_unless(in_array($field, $allFields), RichTextException::unknownRichTextFieldOnEagerLoading($field)))
            ->map(fn ($field) => static::fieldToRichTextRelationship($field))
            ->all();

        $query->with($fields);
    }
}
