<?php

namespace Tonysm\RichTextLaravel;

use DOMElement;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Tonysm\RichTextLaravel\Exceptions\MalformedJsonException;

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
        return $this->fromJson($this->node, 'data-trix-attachment');
    }

    private function composedAttributes(): array
    {
        return $this->fromJson($this->node, 'data-trix-attributes');
    }

    private function fromJson(DOMElement $node, string $key, string $default = "[]")
    {
        $result = json_decode($node->getAttribute($key) ?: $default, true);

        return match (json_last_error()) {
            JSON_ERROR_NONE => $result,
            default => throw MalformedJsonException::failedToParseJson($node, $key),
        };
    }
}
