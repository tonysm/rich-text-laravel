<?php

namespace Tonysm\RichTextLaravel\Actions;

use DOMXPath;
use Exception;
use Tonysm\RichTextLaravel\AttachableFactory;
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

        $attachments = $xpath->query(TrixContent::ATTACHMENT_SELECTOR);

        if ($attachments !== false) {
            /** @var \DOMElement $attachment */
            foreach ($attachments as $attachment) {
                $attachment->parentNode->replaceChild(
                    AttachableFactory::fromNode($attachment)->toDOMElement($doc, $doc->createElement('rich-text-attachable')),
                    $attachment,
                );
            }
        }

        $content = $doc->saveHTML();

        $content;

        if ($content === false) {
            throw new Exception('Something went wrong.');
        }

        return $content;
    }
}
