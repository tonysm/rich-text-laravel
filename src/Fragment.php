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

    public static function wrap($fragmentOrHtml)
    {
        if ($fragmentOrHtml instanceof Fragment) {
            return $fragmentOrHtml;
        }

        if ($fragmentOrHtml instanceof DOMDocument) {
            return new static($fragmentOrHtml);
        }

        return static::fromHtml($fragmentOrHtml);
    }

    public static function fromHtml(string $html)
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

    public function update(): static
    {
        return static::wrap($this->source->saveHTML());
    }

    public function replace(string $selector, callable $callback): static
    {
        $fragment = $this->update();

        $fragment->findAll($selector)
            ->each(function (DOMNode $node) use ($callback) {
                $value = $callback($node);

                if ($value instanceof Fragment) {
                    $newNode = $value->source;

                    if ($importedNode = $node->ownerDocument->importNode($newNode, deep: true)) {
                        $importedNode->loadHTML(trim($newNode->ownerDocument->saveHTML()));
                        $node->ownerDocument->replaceChild($importedNode, $node);
                    }
                } elseif (is_string($value)) {
                    $newNode = $node->ownerDocument->createTextNode($value);
                    $node->parentNode->replaceChild($newNode, $node);
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
