<?php

namespace Tonysm\RichTextLaravel\Attachables;

use DOMElement;
use Tonysm\RichTextLaravel\Content;

class ContentAttachment implements AttachableContract
{
    private Content $contentInstance;

    private string $renderedHtml;

    public static function fromNode(DOMElement $node): ?static
    {
        if (! $node->hasAttribute('content-type') || ! $node->hasAttribute('content')) {
            return null;
        }

        $contentType = $node->getAttribute('content-type');
        $content = trim($node->getAttribute('content'));

        if (str_contains($contentType, 'html') && ($content !== '' && $content !== '0')) {
            return new static($contentType, $content);
        }
    }

    public function __construct(
        public string $contentType,
        public string $content,
    ) {}

    public function toRichTextAttributes(array $attributes): array
    {
        return [
            'contentType' => $this->contentType,
            'content' => $this->content,
        ];
    }

    public function equalsToAttachable(AttachableContract $attachable): bool
    {
        return $attachable instanceof static
            && $attachable->contentType === $this->contentType
            && $attachable->content === $this->content;
    }

    public function richTextAsPlainText(): string
    {
        return $this->contentInstance()->fragment->source->textContent;
    }

    public function richTextRender(array $options = []): string
    {
        return view('rich-text-laravel::attachables._content', [
            'content' => $this,
            'options' => $options,
        ])->render();
    }

    public function renderTrixContentAttachment(array $options = []): string
    {
        return $this->renderedHtml ??= $this->contentInstance()->fragment->toHtml();
    }

    private function contentInstance(): Content
    {
        return $this->contentInstance ??= new Content($this->content);
    }
}
