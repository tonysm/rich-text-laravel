<?php

namespace Tonysm\RichTextLaravel;

use DOMDocumentFragment;
use DOMNode;
use DOMXPath;
use Illuminate\Support\Collection;

class Fragment
{
    private $cachedPlainText;
    private $cachedHtml;

    public static function wrap($fragmentOrHtml)
    {
        if ($fragmentOrHtml instanceof Fragment) {
            return $fragmentOrHtml;
        }

        if ($fragmentOrHtml instanceof DOMDocumentFragment) {
            return new static($fragmentOrHtml);
        }

        return static::fromHtml($fragmentOrHtml);
    }

    public static function fromHtml(string $html)
    {
        return HtmlConversion::fragmentForHtml($html);
    }

    public function __construct(public DOMDocumentFragment $source)
    {
    }

    public function findAll(string $selector): Collection
    {
        $xpath = new DOMXPath($this->source->ownerDocument);

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

    public function update(): static
    {
        return static::wrap($this->source->ownerDocument->saveHTML());
    }

    public function replace(string $selector, callable $callback): static
    {
        $fragment = $this->update();

        $fragment->findAll($selector)
            ->each(function (DOMNode $node) use ($callback) {
                /** @var \DOMDocumentFragment $newNode */
                $newNode = $callback($node)->source;

                if ($importedNode = $node->ownerDocument->importNode($newNode, deep: true)) {
                    $importedNode->appendXML(trim($newNode->ownerDocument->saveHTML()));
                    $node->ownerDocument->replaceChild($importedNode, $node);
                }
            });

        return $fragment;
    }

    public function toPlainText(): string
    {
        return $this->cachedPlainText ??= PlainTextConversion::nodeToPlainText($this->source->parentNode);
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
