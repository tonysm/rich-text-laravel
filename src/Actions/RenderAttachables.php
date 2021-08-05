<?php

namespace Tonysm\RichTextLaravel\Actions;

use DOMElement;
use Exception;
use Tonysm\RichTextLaravel\AttachableFactory;
use Tonysm\RichTextLaravel\Document;

class RenderAttachables
{
    public function __invoke(string $content, callable $next)
    {
        return $next($this->render($content));
    }

    public function render(string $content)
    {
        if (! $content) {
            return $content;
        }

        $document = Document::createFromContent($content);

        (new ExtractAttachables($document))->each(function (DOMElement $attachable) use (&$document) {
            $attachable->parentNode->replaceChild(
                AttachableFactory::fromAttachable($attachable)->toDOMElement($document, $document->createElement('rich-text-attachable'), withContent: true),
                $attachable,
            );
        });

        $content = $document->saveHTML();

        if ($content === false) {
            throw new Exception('Something went wrong.');
        }

        return $content;
    }
}
