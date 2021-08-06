<?php

namespace Tonysm\RichTextLaravel;

use DOMElement;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class TrixAttachment
{
    const TAG_NAME = 'figure';
    const SELECTOR = '//*[@data-trix-attachment]';

    const COMPOSED_ATTRIBUTES = ['caption', 'presentation'];
    const ATTRIBUTES = ['sgid', 'contentType', 'url', 'href', 'filename', 'filesize', 'width', 'height', 'previewable', 'content'];

    public static function fromAttributes(array $attributes): static
    {
        $processedAttributes = static::processAttributes($attributes);

        $trixAttachmentAttributes = Arr::only($processedAttributes, static::ATTRIBUTES);
        $trixAttributes = Arr::only($processedAttributes, static::COMPOSED_ATTRIBUTES);

        $trixAttributes = [
            'data-trix-attachment' => json_encode($trixAttachmentAttributes),
            'data-trix-attributes' => json_encode($trixAttributes),
        ];

        return new static(Document::createElement(static::TAG_NAME, $trixAttributes, $processedAttributes['content'] ?? ''));
    }

    private static function processAttributes(array $attributes): array
    {
        $ensureAttributeTypes = fn ($key, $value) => match ($key) {
            "previewable" => $value == "true",
            "filesize", "width", "height" => is_numeric($value) ? intval($value) : $value,
            default => "{$value}",
        };

        return collect($attributes)
            ->mapWithKeys(function ($value, $key) use ($ensureAttributeTypes) {
                // Converts content-type to contentType;
                $newKey = (string) Str::of($key)->camel();

                return [$newKey => $ensureAttributeTypes($newKey, $value)];
            })
            ->all();
    }

    public function __construct(public DOMElement $node)
    {
    }

    public function attributes(): array
    {
        return $this->attachmentAttributes() + $this->composedAttributes();
    }

    private function attachmentAttributes(): array
    {
        return json_decode($this->node->getAttribute('data-trix-attachment') ?: '[]', true);
    }

    private function composedAttributes(): array
    {
        return json_decode($this->node->getAttribute('data-trix-attributes') ?: '[]', true);
    }
}
