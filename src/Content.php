<?php

namespace Tonysm\RichTextLaravel;

use DOMElement;
use DOMNode;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Tonysm\RichTextLaravel\Actions\FragmentByCanonicalizingAttachmentGalleries;

class Content
{
    public Fragment $fragment;

    private $cachedAttachments;

    private $cachedAttachmentNodes;

    private $cachedAttachmentGalleries;

    private $cachedAttachmentGalleryNodes;

    private $cachedGalleryAttachments;

    private $cachedAttachables;

    public static function fromStorage(?string $value = null): self
    {
        return new Content($value ?: '', ['canonicalize' => false]);
    }

    public static function toStorage(?string $value = null)
    {
        return static::fragmentByCanonicalizingContent($value ?: '')->toHtml();
    }

    public static function fragmentByCanonicalizingContent(string $content)
    {
        return (new Pipeline(app()))
            ->send($content)
            ->through([
                Actions\FragmentByCanonicalizingAttachments::class,
                Actions\FragmentByCanonicalizingAttachmentGalleries::class,
            ])
            ->thenReturn();
    }

    public function __construct($content, array $options = [])
    {
        if ($options['canonicalize'] ?? true) {
            $this->fragment = static::fragmentByCanonicalizingContent($content);
        } else {
            $this->fragment = Fragment::wrap($content);
        }
    }

    public function links(): Collection
    {
        return $this->fragment->findAll('//a[@href]')
            ->map(fn (DOMElement $node) => $node->getAttribute('href'))
            ->unique();
    }

    public function attachments(): Collection
    {
        return $this->cachedAttachments ??= $this->attachmentNodes()->map(fn (DOMElement $node) => (
            $this->attachmentForNode($node)
        ));
    }

    public function attachmentGalleries(): Collection
    {
        return $this->cachedAttachmentGalleries ??= $this->attachmentGalleryNodes()->map(fn (DOMElement $node) => (
            $this->attachmentGalleryForNode($node)
        ));
    }

    public function attachables(): Collection
    {
        return $this->cachedAttachables ??= $this->attachmentNodes()->map(fn (DOMElement $node) => (
            AttachableFactory::fromNode($node)
        ));
    }

    public function galleryAttachments(): Collection
    {
        return $this->cachedGalleryAttachments ??= $this->attachmentGalleries()->flatMap(fn (AttachmentGallery $attachmentGallery) => $attachmentGallery->attachments());
    }

    public function renderAttachments(array $options, callable $callback): static
    {
        $content = $this->fragment->replace(Attachment::$SELECTOR, function (DOMNode $node) use ($options, $callback) {
            return $callback($this->attachmentForNode($node, $options));
        });

        return new static($content, ['canonicalize' => false]);
    }

    public function toPlainText(): string
    {
        return $this->renderAttachments(
            ['withFullAttributes' => false],
            fn (Attachment $item) => $item->toPlainText()
        )->fragment->toPlainText();
    }

    public function toTrixHtml()
    {
        return $this->renderAttachments(
            [],
            fn (Attachment $attachment) => (HtmlConversion::fragmentForHtml($attachment->toTrixAttachment()->toHtml()))
        )->toHtml();
    }

    public function toHtml(): string
    {
        return $this->renderAttachments([], fn (Attachment $attachment) => $attachment->toTrixAttachment())
            ->fragment->toHtml();
    }

    public function renderWithAttachments(): string
    {
        return $this->renderAttachments([], function (Attachment $attachment) {
            // If this is a gallery attachment, we'll render it separately.

            if ($this->galleryAttachments()->first(fn (Attachment $galleryAttachment) => $galleryAttachment->is($attachment))) {
                return null;
            }

            return HtmlConversion::fragmentForHtml($this->renderAttachment($attachment, [
                'in_gallery' => false,
            ]));
        })->renderAttachmentGalleries(fn (AttachmentGallery $attachmentGallery) => (
            $attachmentGallery->richTextRender()
        ))->fragment->toHtml();
    }

    public function renderAttachmentGalleries(callable $renderer): static
    {
        $content = (new FragmentByCanonicalizingAttachmentGalleries)->fragmentByReplacingAttachmentGalleryNodes($this->fragment, function (DOMElement $node) use ($renderer) {
            return HtmlConversion::document($renderer($this->attachmentGalleryForNode($node)));
        });

        return new static($content, ['canonicalize' => false]);
    }

    public function renderAttachment(Attachment $attachment, array $locals = []): string
    {
        return $attachment->attachable->richTextRender(options: $locals);
    }

    public function render()
    {
        return view('rich-text-laravel::content', [
            'content' => $this,
        ])->render();
    }

    public function raw(): string
    {
        return $this->fragment->toHtml();
    }

    public function isEmpty(): bool
    {
        return empty(trim($this->toHtml()));
    }

    public function __toString(): string
    {
        return $this->render();
    }

    private function attachmentNodes(): Collection
    {
        return $this->cachedAttachmentNodes ??= $this->fragment->findAll(Attachment::$SELECTOR);
    }

    private function attachmentGalleryNodes(): Collection
    {
        return $this->cachedAttachmentGalleryNodes ??= (new FragmentByCanonicalizingAttachmentGalleries)->findAttachmentGalleryNodes($this->fragment);
    }

    private function attachmentGalleryForNode(DOMElement $node)
    {
        return AttachmentGallery::fromNode($node);
    }

    private function attachmentForNode(DOMNode $node, array $options = []): Attachment
    {
        $attachment = Attachment::fromNode($node);

        if ($options['withFullAttributes'] ?? false) {
            return $attachment->withFullAttributes();
        }

        return $attachment;
    }
}
