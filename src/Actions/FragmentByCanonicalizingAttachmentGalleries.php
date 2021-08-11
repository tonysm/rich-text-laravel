<?php

namespace Tonysm\RichTextLaravel\Actions;

use DOMElement;
use DOMNode;
use DOMText;
use Illuminate\Support\Collection;
use Tonysm\RichTextLaravel\AttachmentGallery;
use Tonysm\RichTextLaravel\Fragment;

class FragmentByCanonicalizingAttachmentGalleries
{
    public function __invoke($content, callable $next)
    {
        return $next($this->parse($content));
    }

    public function parse($content)
    {
        return $content;
    }

    public function findAttachmentGalleryNodes($content): Collection
    {
        return Fragment::wrap($content)
            ->findAll(AttachmentGallery::selector())
            ->filter(function (DOMElement $node) {
                // We are only interested in DIVs that only contain rich-text-attachment
                // tags. But they may contain empty texts as well, we can ignore them
                // when converting the gallery attachments node objects later on.

                foreach ($node->childNodes as $child) {
                    if (! $this->galleryAttachmentNode($child) && ! $this->emptyTextNode($child)) {
                        return false;
                    }
                }

                return true;
            });
    }

    private function galleryAttachmentNode(DOMNode $node): bool
    {
        return $node instanceof DOMElement
            && $node->hasAttribute('presentation')
            && $node->getAttribute('presentation') === 'gallery';
    }

    private function emptyTextNode(DOMNode $node): bool
    {
        return $node instanceof DOMText
            && empty(trim($node->textContent));
    }
}
