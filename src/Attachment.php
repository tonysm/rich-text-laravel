<?php

namespace Tonysm\RichTextLaravel;

use DOMElement;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;
use Tonysm\RichTextLaravel\Attachables\AttachableContract;

class Attachment
{
    use ForwardsCalls;

    const TAG_NAME = 'rich-text-attachment';
    const ATTRIBUTES = ['sgid', 'content-type', 'url', 'href', 'filename', 'filesize', 'width', 'height', 'previewable', 'presentation', 'caption'];

    public static function fromAttachable(AttachableContract $attachable, array $attributes = []): ?static
    {
        if ($node = static::fromAttributes($attachable->toRichTextAttributes($attributes))) {
            return new static($node, $attachable);
        }

        return null;
    }

    public static function fromNode(DOMElement $node): static
    {
        return new static($node, GlobalId::fromStorage($node->getAttribute('sgid')));
    }

    private static function fromAttributes(array $attributes = [])
    {
        if ($attributes = static::processAttributes($attributes)) {
            return Document::createElement(static::TAG_NAME, $attributes);
        }
    }

    private static function processAttributes(array $attributes): array
    {
        return collect($attributes)
            ->mapWithKeys(function ($value, $key) {
                $newKey = (string) Str::of($key)->studly()->snake('-');

                return [$newKey => $value];
            })
            ->filter(fn ($item, $key) => in_array($key, static::ATTRIBUTES))
            ->all();
    }

    public function __construct(public DOMElement $node, public AttachableContract $attachable)
    {
    }

    public function caption()
    {
        return $this->node->getAttribute('caption');
    }

    public function toTrixAttachment(?string $content = null): TrixAttachment
    {
        $nodeAttributes = collect(static::ATTRIBUTES)
            ->mapWithKeys(fn ($attr) => [$attr => $this->node->getAttribute($attr)])
            ->filter()
            ->all();

        $content = $content !== null ? $content : $this->attachable->richTextRender();
        $nodeAttributes['content'] = $content;

        return TrixAttachment::fromAttributes($this->attachable->toRichTextAttributes($nodeAttributes));
    }

    public function toPlainText(): string
    {
        if (method_exists($this->attachable, 'plainTextRender')) {
            return $this->attachable->plainTextRender();
        }

        return $this->caption();
    }

    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->attachable, $method, $parameters);
    }
}
