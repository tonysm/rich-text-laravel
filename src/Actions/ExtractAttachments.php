<?php

namespace Tonysm\RichTextLaravel\Actions;

use DOMXPath;
use Tonysm\RichTextLaravel\Attachment;
use Tonysm\RichTextLaravel\Document;
use Tonysm\RichTextLaravel\TrixAttachment;

class ExtractAttachments
{
    public function __invoke(string $content, callable $next)
    {
        return $next($this->parse($content));
    }

    public function parse(string $content): string
    {
        if (empty($content)) {
            return '';
        }

        $document = Document::createDocument($content);
        $xpath = new DOMXPath($document);

        $this->replaceAttachments($xpath);

        return $document->saveHTML();
    }

    private function replaceAttachments(DOMXPath $xpath): void
    {
        $attachments = $xpath->query(TrixAttachment::SELECTOR);

        if ($attachments === false) {
            return;
        }

        /** @var \DOMElement $node */
        foreach ($attachments as $node) {
            $attachmentNode = Attachment::nodeFromAttributes((new TrixAttachment($node))->attributes());
            $importedNode = $xpath->document->importNode($attachmentNode, true);
            $node->replaceWith($importedNode);
        }
    }
}
