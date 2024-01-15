<?php

namespace Tonysm\RichTextLaravel;

use DOMDocument;
use DOMNode;
use DOMXPath;
use Illuminate\Support\Collection;

class Fragment
{
    private $cachedPlainText;

    private $cachedHtml;

    public static function wrap(string|Fragment|DOMDocument $fragmentOrHtml)
    {
        if ($fragmentOrHtml instanceof Fragment) {
            return $fragmentOrHtml;
        }

        if ($fragmentOrHtml instanceof DOMDocument) {
            return new static($fragmentOrHtml);
        }

        return static::fromHtml($fragmentOrHtml);
    }

    public static function fromHtml(?string $html = null): self
    {
        return HtmlConversion::fragmentForHtml($html);
    }

    public function __construct(public DOMDocument $source)
    {
    }

    public function findAll(string $selector): Collection
    {
        $xpath = new DOMXPath($this->source);

        $elements = $xpath->query($selector);

        if ($elements === false) {
            return collect([]);
        }

        $result = collect([]);

        foreach ($elements as $element) {
            $result->add($element);
        }

        return $result;
    }

    public function update(?callable $callback = null): static
    {
        $callback = $callback ?: fn ($source) => $source;

        return new static($callback($this->source->cloneNode(deep: true)));
    }

    public function replace(string $selector, callable $callback): static
    {
        $fragment = $this->update();

        $fragment->findAll($selector)
            ->each(function (DOMNode $node) use ($callback) {
                $value = $callback($node);

                if ($value instanceof Fragment) {
                    // Each fragment source is wrapped in a div, so we can ignore it when appending.
                    $newNode = $value->source;

                    foreach ($newNode->firstChild->childNodes as $child) {
                        if ($importedNode = $node->ownerDocument->importNode($child, deep: true)) {
                            $node->parentNode->insertBefore($importedNode, $node);
                        }
                    }

                    $node->parentNode->removeChild($node);
                } elseif (is_string($value)) {
                    $newNode = $node->ownerDocument->createTextNode($value);
                    $node->parentNode->replaceChild($newNode, $node);
                } elseif ($value instanceof Attachment) {
                    if ($importedNode = $node->ownerDocument->importNode($value->node, deep: true)) {
                        $node->parentNode->insertBefore($importedNode, $node);
                    }

                    $node->parentNode->removeChild($node);
                }
            });

        return $fragment;
    }

    public function toPlainText(): string
    {
        return $this->cachedPlainText ??= PlainTextConversion::nodeToPlainText($this->source);
    }

    public function toHtml(): string
    {
        return $this->cachedHtml ??= HtmlConversion::nodeToHtml($this->source);
    }

    public function __toString(): string
    {
        return $this->toHtml();
    }
}
