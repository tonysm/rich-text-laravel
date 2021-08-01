<?php

namespace Tonysm\RichTextLaravel\Actions;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Exception;
use Tonysm\RichTextLaravel\AttachableFactory;
use Tonysm\RichTextLaravel\TrixContent;

class RenderAttachables
{
    public function __invoke(string $content, callable $next)
    {
        return $next($this->render($content));
    }

    public function render(string $content)
    {
        $doc = null;

        (new ExtractAttachables)($content, function (DOMElement $attachable, DOMDocument $document) use (&$doc) {
            $attachable->parentNode->replaceChild(
                AttachableFactory::fromAttachable($attachable)->toDOMElement($document, $document->createElement('rich-text-attachable'), withContent: true),
                $attachable,
            );

            $doc ??= $document;
        });

        if ($doc === null) {
            return '';
        }

        $content = $doc->saveHTML();

        if ($content === false) {
            throw new Exception('Something went wrong.');
        }

        return $content;
    }
}
