<?php

namespace Tonysm\RichTextLaravel;

use DOMNode;
use Illuminate\Pipeline\Pipeline;

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
        if ($options['canonicalize'] ?? false) {
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

    private function attachmentForNode(DOMNode $node, array $options = []): Attachment
    {
        $attachment = Attachment::fromNode($node);

        if ($options['withFullAttributes'] ?? false) {
            return $attachment->withFullAttributes();
        }

        return $attachment;
    }
}
