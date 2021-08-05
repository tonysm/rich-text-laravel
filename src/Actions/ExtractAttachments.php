<?php

namespace Tonysm\RichTextLaravel\Actions;

use DOMXPath;
use Exception;
use Tonysm\RichTextLaravel\AttachableFactory;
use Tonysm\RichTextLaravel\Attachables\ImageGallery;
use Tonysm\RichTextLaravel\Document;
use Tonysm\RichTextLaravel\TrixContent;

class ExtractAttachments
{
    public function __invoke(string $content, callable $next): string
    {
        return $next($this->parse($content));
    }

    public function parse(string $content): string
    {
        if (empty($content)) {
            return "";
        }

        $doc = Document::createFromContent($content);
        $xpath = new DOMXPath($doc);

        $this->replaceImageGalleries($xpath);
        $this->replaceAttachments($xpath);

        $content = $doc->saveHTML();

        if ($content === false) {
            throw new Exception('Something went wrong.');
        }

        return $content;
    }

    private function replaceImageGalleries(DOMXPath $xpath)
    {
        $galleries = $xpath->query(TrixContent::GALLERY_SELECTOR);

        if ($galleries !== false) {
            /** @var \DOMElement $gallery */
            foreach ($galleries as $gallery) {
                $images = $xpath->query(TrixContent::ATTACHMENT_SELECTOR, $gallery);

                $data = [];

                foreach ($images as $image) {
                    $data[] = AttachableFactory::fromNode($image);
                }

                $gallery->parentNode->replaceChild(
                    ImageGallery::fromNode($data, $gallery)->toDOMElement($xpath->document, $xpath->document->createElement('rich-text-attachable')),
                    $gallery,
                );
            }
        }
    }

    private function replaceAttachments(DOMXPath $xpath)
    {
        $attachments = $xpath->query(TrixContent::ATTACHMENT_SELECTOR);

        if ($attachments !== false) {
            /** @var \DOMElement $attachment */
            foreach ($attachments as $attachment) {
                $attachment->parentNode->replaceChild(
                    AttachableFactory::fromNode($attachment)->toDOMElement($xpath->document, $xpath->document->createElement('rich-text-attachable')),
                    $attachment,
                );
            }
        }
    }
}
