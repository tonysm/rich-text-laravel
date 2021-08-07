<?php

namespace Tonysm\RichTextLaravel\Actions\Rendering;

use DOMXPath;
use Tonysm\RichTextLaravel\Attachment;
use Tonysm\RichTextLaravel\Document;

class InjectAttachmentContent
{
    public function __construct(private bool $plainText = false)
    {
    }

    public function __invoke(string $content, callable $next): string
    {
        return $next($this->parse($content));
    }

    public function parse(string $content): string
    {
        if (! $content) {
            return $content;
        }

        $document = Document::createDocument("<body>{$content}</body>");
        $xpath = new DOMXPath($document);

        $fragments = $xpath->query(Attachment::$SELECTOR);

        if ($fragments === false) {
            return $content;
        }

        /** @var \DOMElement $fragment */
        foreach ($fragments as $fragment) {
            $attachment = Attachment::fromNode($fragment);

            $newFragment = Document::createDocument(
                $this->plainText ? $attachment->toPlainText() : $attachment->richTextRender()
            );

            if ($importedNode = $document->importNode($newFragment->documentElement, true)) {
                $fragment->appendChild($importedNode);
            }
        }

        return preg_replace('#</?body>\n?#', '', $document->saveHTML());
    }
}
