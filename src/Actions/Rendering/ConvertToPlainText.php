<?php

namespace Tonysm\RichTextLaravel\Actions\Rendering;

use DOMNode;
use DOMNodeList;
use DOMText;
use DOMXPath;
use Tonysm\RichTextLaravel\Document;

class ConvertToPlainText
{
    public function __invoke(string $content, callable $next): string
    {
        return $next($this->parse($content));
    }

    public function parse(string $content): string
    {
        if (! $content) {
            return $content;
        }

        $document = Document::createDocument("<body>$content</body>");
        $xpath = new DOMXPath($document);

        $fragments = $xpath->query('//body/*|//body/text()');

        if ($fragments === false) {
            return $content;
        }

        return trim($this->renderNodeList($fragments));
    }

    private function renderNodeList(DOMNodeList $fragments): string
    {
        $content = '';

        foreach ($fragments as $fragment) {
            $content .= $this->renderFragment($fragment);
        }

        return $content;
    }

    private function renderFragment(DOMNode $node): string
    {
        if ($node instanceof DOMText) {
            return $node->textContent;
        }

        return match ($node->nodeName) {
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p' => sprintf("%s\n\n", $node->childNodes->length > 1 ? $this->renderNodeList($node->childNodes) : $node->textContent),
            'div' => sprintf("%s\n", $node->childNodes->length > 1 ? $this->renderNodeList($node->childNodes) : $node->textContent),
            'blockquote' => sprintf("“%s”\n\n", $node->textContent),
            'ol', 'ul' => sprintf("%s\n\n", $this->renderListItems($node->childNodes, ordered: $node->nodeName === 'ol')),
            'li' => sprintf("• %s\n", $node->textContent),
            'br' => "\n",
            'strong', 'span', 'em', 'i' => $node->childNodes->length > 1 ? $this->renderNodeList($node->childNodes) : $node->textContent,
            'figcaption' => sprintf("[%s]", $node->textContent),
            'rich-text-attachment' => trim($this->renderNodeList($node->childNodes)),
        };
    }

    private function renderListItems(DOMNodeList $fragments, bool $ordered = false)
    {
        $content = '';
        $index = 1;

        foreach ($fragments as $fragment) {
            $content .= sprintf(
                "%s %s\n",
                $ordered ? ($index++ . '.') : '•',
                $fragment->textContent,
            );
        }

        return trim($content);
    }
}
