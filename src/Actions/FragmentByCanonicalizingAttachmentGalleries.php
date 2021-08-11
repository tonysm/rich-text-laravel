<?php

namespace Tonysm\RichTextLaravel\Actions;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use Illuminate\Support\Collection;
use Tonysm\RichTextLaravel\AttachmentGallery;
use Tonysm\RichTextLaravel\Fragment;
use Tonysm\RichTextLaravel\HtmlConversion;

class FragmentByCanonicalizingAttachmentGalleries
{
    public function __invoke($content, callable $next)
    {
        return $next($this->fragmentByReplacingAttachmentGalleryNodes($content, function (DOMElement $node) {
            return HtmlConversion::document(sprintf(
                '<%s>%s</%s>',
                AttachmentGallery::TAG_NAME,
                $this->getInnerHtmlOfNode($node),
                AttachmentGallery::TAG_NAME,
            ));
        }));
    }

    public function fragmentByReplacingAttachmentGalleryNodes($content, callable $callback): Fragment
    {
        return Fragment::wrap($content)->update(function (DOMDocument $source) use ($callback) {
            $this->findAttachmentGalleryNodes($source)->each(function (DOMElement $node) use ($source, $callback) {
                // The fragment is wrapped with a rich-text-root tag, so we need
                // to dig a bit deeper to get to the attachment gallery.

                $newNode = $callback($node)->firstChild->firstChild;

                if ($importedNode = $source->importNode($newNode, deep: true)) {
                    $node->replaceWith($importedNode);
                }
            });

            return $source;
        });
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

    private function getInnerHtmlOfNode(DOMElement $node): string
    {
        $innerContent = '';

        foreach ($node->childNodes as $child) {
            $innerContent .= $child->ownerDocument->saveHtml($child);
        }

        return $innerContent;
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
