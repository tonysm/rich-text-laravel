<?php

namespace Tonysm\RichTextLaravel\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Tonysm\RichTextLaravel\Casts\AsEncryptedRichTextContent;
use Tonysm\RichTextLaravel\Casts\AsRichTextContent;
use Tonysm\RichTextLaravel\Casts\ForwardsAttributeToRelationship;
use Tonysm\RichTextLaravel\Exceptions\RichTextException;

trait HasRichText
{
    protected static function bootHasRichText()
    {
        if (method_exists(static::class, 'whenBooted')) {
            static::whenBooted(function () {
                static::configureDynamicRelationshipsForRichTextFields();
                static::registerModelEventsForRichTextFields();
            });
        } else {
            static::configureDynamicRelationshipsForRichTextFields();
            static::registerModelEventsForRichTextFields();
        }
    }

    protected static function registerModelEventsForRichTextFields(): void
    {
        static::saving(function (Model $model): void {
            if (! $model::isIgnoringTouch()) {
                foreach ($model->getRichTextFields() as $field => $options) {
                    if ($options['attribute'] ?? false) {
                        continue;
                    }

                    $relationship = static::fieldToRichTextRelationship($field);

                    if ($model->relationLoaded($relationship) && $model->{$field}->isDirty() && $model->timestamps) {
                        $model->updateTimestamps();
                    }
                }
            }
        });

        static::saved(function (Model $model): void {
            foreach ($model->getRichTextFields() as $field => $options) {
                if ($options['attribute'] ?? false) {
                    continue;
                }

                $relationship = static::fieldToRichTextRelationship($field);

                if ($model->relationLoaded($relationship) && $model->{$field}->isDirty()) {
                    $model->{$field}->record()->associate($model);
                    $model->{$field}->save();
                }
            }
        });
    }

    protected static function configureDynamicRelationshipsForRichTextFields(): void
    {
        $fields = (new static)->getRichTextFields();

        foreach ($fields as $field => $options) {
            static::registerRichTextRelationships($field, $options);
        }
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
        foreach ($this->getRichTextFields() as $field => $options) {
            if ($options['attribute'] ?? false) {
                $cast = ($options['encrypted'] ?? false)
                    ? AsEncryptedRichTextContent::class
                    : AsRichTextContent::class;
            } else {
                $cast = ForwardsAttributeToRelationship::class;
            }

            $this->mergeCasts([
                $field => $cast,
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

    public function unsetRichTextRelationshipsForLivewireDehydration(): void
    {
        $fields = collect($this->getRichTextFields())
            ->filter(fn ($options) => ! ($options['attribute'] ?? false))
            ->keys()
            ->map(fn ($field): string => static::fieldToRichTextRelationship($field));

        foreach ($fields as $relationship) {
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
        $richTextFields = (new static)->getRichTextFields();
        $allFields = array_keys($richTextFields);

        $fields = empty($fields) ? $allFields : $fields;

        // Converts fields to relationships for eager loading and validates them;
        // fields with 'attribute' => true are on the model, so we skip them.
        // Any unknown fields passed here will throw an exception and halt.

        $fields = collect($fields)
            ->each(fn ($field) => throw_unless(in_array($field, $allFields), RichTextException::unknownRichTextFieldOnEagerLoading($field)))
            ->filter(fn ($field) => ! ($richTextFields[$field]['attribute'] ?? false))
            ->map(fn ($field): string => static::fieldToRichTextRelationship($field))
            ->all();

        $query->with($fields);
    }
}
