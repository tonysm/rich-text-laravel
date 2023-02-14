<?php

namespace Tonysm\RichTextLaravel;

use DOMElement;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class TrixAttachment
{
    public static $TAG_NAME = 'figure';

    public static $SELECTOR = '//*[@data-trix-attachment]';

    const COMPOSED_ATTRIBUTES = ['caption', 'presentation'];

    const ATTRIBUTES = [...self::COMPOSED_ATTRIBUTES, 'sgid', 'contentType', 'url', 'href', 'filename', 'filesize', 'width', 'height', 'previewable', 'content'];

    private $attributesCache;

    public static function fromAttributes(array $attributes): static
    {
        $attributes = static::processAttributes($attributes);

        $trixAttachmentAttributes = Arr::except($attributes, static::COMPOSED_ATTRIBUTES);
        $trixAttributes = Arr::only($attributes, static::COMPOSED_ATTRIBUTES);

        $node = HtmlConversion::createElement(static::$TAG_NAME);
        $node->setAttribute('data-trix-attachment', json_encode($trixAttachmentAttributes));

        if ($trixAttributes) {
            $node->setAttribute('data-trix-attributes', json_encode($trixAttributes ?: []));
        }

        return new static($node);
    }

    private static function processAttributes(array $attributes): array
    {
        return collect($attributes)
            ->mapWithKeys(function ($value, $key) {
                $newKey = (string) Str::of($key)->camel();

                return [$newKey => static::typeCast($newKey, $value)];
            })
            ->only(static::ATTRIBUTES)
            ->all();
    }

    private static function typeCast(string $key, $value)
    {
        return match ($key) {
            'previewable' => $value === true || $value === 'true',
            'filesize', 'height', 'width' => is_numeric($value) ? intval($value) : $value,
            default => "{$value}",
        };
    }

    public function __construct(public DOMElement $node)
    {
    }

    public function attributes(): array
    {
        return $this->attributesCache ??= collect($this->attachmentAttributes())
            ->merge($this->composedAttributes())
            ->only(static::ATTRIBUTES)
            ->all();
    }

    public function toHtml(): string
    {
        return $this->node->ownerDocument->saveHTML($this->node);
    }

    private function attachmentAttributes(): array
    {
        return $this->readJsonAttribute('data-trix-attachment');
    }

    private function composedAttributes(): array
    {
        return $this->readJsonAttribute('data-trix-attributes');
    }

    private function readJsonAttribute(string $key): array
    {
        if (! $this->node->hasAttribute($key)) {
            return [];
        }

        $value = $this->node->getAttribute($key);
        $data = json_decode($value ?: '[]', true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            logger(sprintf(
                '[%s] Couldnt parse JSON %s from NODE %s',
                static::class,
                $value,
                $this->node->tagName,
            ));

            return [];
        }

        return $data;
    }
}
