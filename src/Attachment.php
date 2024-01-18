<?php

namespace Tonysm\RichTextLaravel;

use DOMElement;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;
use Tonysm\RichTextLaravel\Attachables\AttachableContract;
use Tonysm\RichTextLaravel\Attachments\TrixConvertion;

class Attachment
{
    use ForwardsCalls;
    use TrixConvertion;

    public static $TAG_NAME = 'rich-text-attachment';

    public static $SELECTOR = '//rich-text-attachment';

    const ATTRIBUTES = ['sgid', 'content-type', 'url', 'href', 'filename', 'filesize', 'width', 'height', 'previewable', 'presentation', 'caption'];

    private $cachedAttributes;

    private $cachedSgidAttributes;

    private $cachedAttachableAttributes;

    public static function useTagName(string $tagName): void
    {
        static::$SELECTOR = str_replace(static::$TAG_NAME, $tagName, static::$SELECTOR);
        static::$TAG_NAME = $tagName;
    }

    public static function fromAttachable(AttachableContract $attachable, array $attributes = []): ?static
    {
        if ($node = static::nodeFromAttributes($attachable->toRichTextAttributes($attributes))) {
            return new static($node, $attachable);
        }

        return null;
    }

    public static function fromNode(DOMElement $node, ?AttachableContract $attachable = null): static
    {
        return new static($node, $attachable ?: AttachableFactory::fromNode($node));
    }

    /**
     * @return null|static
     */
    public static function fromAttributes(array $attributes = [], ?AttachableContract $attachable = null)
    {
        if ($node = static::nodeFromAttributes($attributes)) {
            return static::fromNode($node, $attachable);
        }
    }

    public static function nodeFromAttributes(array $attributes = []): ?DOMElement
    {
        $attributes = static::processAttributes($attributes);

        if (empty($attributes)) {
            return null;
        }

        return HtmlConversion::createElement(static::$TAG_NAME, $attributes);
    }

    private static function processAttributes(array $attributes): array
    {
        return collect($attributes)
            ->mapWithKeys(function ($value, $key) {
                $newKey = (string) Str::of($key)->camel()->snake('-');

                return [$newKey => $value];
            })
            ->only(static::ATTRIBUTES)
            ->all();
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

    public function toHtml(): string
    {
        return HtmlConversion::nodeElementToHtml($this->node);
    }

    public function fullAttributes(): Collection
    {
        return $this->nodeAttributes()
            ->merge($this->attachableAttributes())
            ->merge($this->sgidAttributes());
    }

    private function attachableAttributes(): Collection
    {
        return $this->cachedAttachableAttributes ??= collect(
            $this->attachable->toRichTextAttributes([])
        );
    }

    private function sgidAttributes(): Collection
    {
        return $this->cachedSgidAttributes ??= collect([
            'sgid' => $this->nodeAttributes()->get('sgid', $this->attachableAttributes()->get('sgid')),
        ])->filter();
    }

    private function nodeAttributes(): Collection
    {
        return $this->cachedAttributes ??= collect(static::ATTRIBUTES)
            ->mapWithKeys(function ($key) {
                $newKey = (string) Str::of($key)->snake();

                return [$newKey => $this->node->hasAttribute($newKey) ? $this->node->getAttribute($newKey) : null];
            });
    }

    public function is(Attachment $attachment): bool
    {
        return $this->attachable->equalsToAttachable($attachment->attachable);
    }

    public function __toString()
    {
        return $this->toHtml();
    }

    public function __call($method, $arguments)
    {
        return $this->forwardCallTo($this->attachable, $method, $arguments);
    }
}
