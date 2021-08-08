<?php

namespace Tonysm\RichTextLaravel;

use DOMElement;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Tonysm\RichTextLaravel\Attachables\AttachableContract;

class Attachment
{
    use TrixConvertion;

    public static $TAG_NAME = 'rich-text-attachment';
    public static $SELECTOR = '//rich-text-attachment';

    const ATTRIBUTES = ['sgid', 'content-type', 'url', 'href', 'filename', 'filesize', 'width', 'height', 'previewable', 'presentation', 'caption'];

    private $cachedAttributes;

    public static function fromNode(DOMElement $node, AttachableContract $attachable = null): static
    {
        return new static($node, $attachable ?: AttachableFactory::fromNode($node));
    }

    public function __construct(public DOMElement $node, public AttachableContract $attachable)
    {
    }

    public function withFullAttributes(): static
    {
        return $this;
    }

    public function caption()
    {
        return $this->nodeAttributes()['caption'] ?? null;
    }

    public function toPlainText(): string
    {
        if (method_exists($this->attachable, 'richTextAsPlainText')) {
            return $this->attachable->richTextAsPlainText($this->caption());
        }

        return $this->caption() ?: '';
    }

    public function fullAttributes(): Collection
    {
        return $this->nodeAttributes()
            ->merge($this->attachableAttributes())
            ->merge($this->sgidAttributes());
    }

    private function attachableAttributes(): Collection
    {
        return $this->cachedAttachableAttributes ??= method_exists($this->attachable, 'toRichTextAttributes')
            ? $this->attachable->toRichTextAttributes()
            : collect([]);
    }

    private function sgidAttributes(): Collection
    {
        return $this->cachedSgidAttributes ??= $this->nodeAttributes()
            ->get('sgid', $this->attachableAttributes()->get('sgid'))
            ->filter();
    }

    private function nodeAttributes(): Collection
    {
        return $this->cachedAttributes ??= collect(static::ATTRIBUTES)
            ->mapWithKeys(function ($key) {
                $newKey = (string) Str::of($key)->snake();

                return [$newKey => $this->node->hasAttribute($newKey) ? $this->node->getAttribute($newKey) : null];
            });
    }
}
