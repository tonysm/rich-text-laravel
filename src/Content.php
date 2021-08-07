<?php

namespace Tonysm\RichTextLaravel;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Collection;
use Tonysm\RichTextLaravel\Actions\RenderAttachments;

class Content
{
    private string $content;

    public static function canonicalizingContent(string $content): string
    {
        return (new ParseAttachments)($content);
    }

    public static function toStorage(string $content): string
    {
        return static::canonicalizingContent($content);
    }

    public static function fromStorage(string $content): static
    {
        return new static($content, ['canonicalize' => false]);
    }

    public function __construct(string $content, array $options = [])
    {
        $canonicalize = $options['canonicalize'] ?? true;

        if ($canonicalize) {
            $this->content = static::canonicalizingContent($content);
        } else {
            $this->content = $content;
        }
    }

    public function links(): array
    {
        $xpath = new DOMXPath($this->document());

        $links = collect([]);

        foreach ($xpath->query('//a[@href]') as $linkNode) {
            $links->add($linkNode->getAttribute('href'));
        }

        return $links->unique()->values()->all();
    }

    public function attachments(): Collection
    {
        return $this->attachmentNodes()->map(fn (DOMElement $node) => (
            Attachment::fromNode($node)
        ));
    }

    private function attachmentNodes(): Collection
    {
        $xpath = new DOMXPath($this->document());

        $nodes = collect([]);

        foreach ($xpath->query(Attachment::$SELECTOR) as $node) {
            $nodes->add($node);
        }

        return $nodes;
    }

    public function renderWithAttachments(bool $plainText = false)
    {
        return (new RenderAttachments(plainText: $plainText, withContents: true))($this->content);
    }

    public function renderAttachmentsWithoutContent(bool $plainText = false)
    {
        return (new RenderAttachments(plainText: $plainText, withContents: false))($this->content);
    }

    public function render(): string
    {
        return view('rich-text-laravel::content', [
            'content' => $this,
        ])->render();
    }

    public function toPlainText(): string
    {
        return $this->renderWithAttachments(plainText: true);
    }

    public function isEmpty(): bool
    {
        return empty(trim($this->content));
    }

    public function raw(): string
    {
        return $this->content;
    }

    public function __toString()
    {
        return $this->render();
    }

    private function document(): DOMDocument
    {
        return Document::createDocument($this->content);
    }
}
