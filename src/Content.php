<?php

namespace Tonysm\RichTextLaravel;

use DOMElement;
use DOMNode;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;

class Content
{
    public Fragment $fragment;

    public static function fragmentByCanonicalizingContent($content)
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

    public function toPlainText(): string
    {
        return $this->renderAttachments(
            ['withFullAttributes' => false],
            fn (Attachment $item) => $item->toPlainText()
        )->fragment->toPlainText();
    }

    public function renderAttachments(array $options, callable $callback): static
    {
        $content = $this->fragment->replace(Attachment::$SELECTOR, function (DOMNode $node) use ($options, $callback) {
            return $callback($this->attachmentForNode($node, $options));
        });

        return new static($content, [
            'canonicalize' => false,
        ]);
    }

    public function links(): array
    {
        return $this->fragment->findAll('//a[@href]')
            ->map(fn (DOMElement $node) => $node->getAttribute('href'))
            ->unique()
            ->all();
    }

    public function attachments(): Collection
    {
        return $this->cachedAttachments ??= $this->attachmentNodes()->map(fn (DOMElement $node) => (
            $this->attachmentForNode($node)
        ));
    }

    private function attachmentNodes(): Collection
    {
        return $this->cachedAttachmentNodes ??= $this->fragment->findAll(Attachment::$SELECTOR);
    }

    private function attachmentForNode(DOMNode $node, array $options = []): Attachment
    {
        $attachment = Attachment::fromNode($node);

        if ($options['withFullAttributes'] ?? false) {
            return $attachment->withFullAttributes();
        }

        return $attachment;
    }

    public function toHtml()
    {
        return preg_replace("#</?rich-text-root>\n?#", "", $this
            ->renderAttachments([], fn (Attachment $attachment) => $attachment->toTrixAttachment())
            ->fragment->toHtml());
    }
}
