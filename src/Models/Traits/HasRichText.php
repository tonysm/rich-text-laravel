<?php

namespace Tonysm\RichTextLaravel\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Tonysm\RichTextLaravel\Casts\ForwardsAttributeToRelationship;
use Tonysm\RichTextLaravel\Exceptions\RichTextException;

trait HasRichText
{
    protected static function bootHasRichText()
    {
        $fields = (new static)->getRichTextFields();

        foreach ($fields as $field => $options) {
            static::registerRichTextRelationships($field, $options);
        }

        static::saving(function (Model $model) {
            if (! $model::isIgnoringTouch()) {
                foreach ($model->getRichTextFields() as $field => $_options) {
                    $relationship = static::fieldToRichTextRelationship($field);

                    if ($model->relationLoaded($relationship) && $model->{$field}->isDirty() && $model->timestamps) {
                        $model->updateTimestamps();
                    }
                }
            }
        });

        static::saved(function (Model $model) {
            foreach ($model->getRichTextFields() as $field => $_options) {
                $relationship = static::fieldToRichTextRelationship($field);

                if ($model->relationLoaded($relationship) && $model->{$field}->isDirty()) {
                    $model->{$field}->record()->associate($model);
                    $model->{$field}->save();
                }
            }
        });
    }

    protected static function registerRichTextRelationships(string $field, array $options = []): void
    {
        static::resolveRelationUsing(static::fieldToRichTextRelationship($field), function (Model $model) use ($field, $options) {
            $modelClass = ($options['encrypted'] ?? false)
                ? config('rich-text-laravel.encrypted_model')
                : config('rich-text-laravel.model');

            return $model->morphOne($modelClass, 'record')->where('field', $field);
        });
    }

    protected function initializeHasRichText()
    {
        foreach ($this->getRichTextFields() as $field => $_options) {
            $this->mergeCasts([
                $field => ForwardsAttributeToRelationship::class,
            ]);
        }
    }

    protected function getRichTextFields(): array
    {
        if (! property_exists($this, 'richTextAttributes')) {
            throw RichTextException::missingRichTextFieldsProperty(static::class);
        }

        $fields = Collection::wrap($this->richTextAttributes);

        return $fields->mapWithKeys(fn ($value, $key) => is_string($key) ? [$key => $value] : [$value => []])->all();
    }

    public function unsetRichTextRelationshipsForLivewireDehydration()
    {
        $relationships = array_map(fn ($field) => static::fieldToRichTextRelationship($field), array_keys($this->getRichTextFields()));

        foreach ($relationships as $relationship) {
            if ($this->relationLoaded($relationship)) {
                $this->unsetRelation($relationship);
            }
        }
    }

    public static function fieldToRichTextRelationship(string $field): string
    {
        return 'richText'.Str::studly($field);
    }

    public function scopeWithRichText(Builder $query, $fields = []): void
    {
        $allFields = array_keys((new static)->getRichTextFields());

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
