<?php

declare(strict_types=1);

namespace Tonysm\RichTextLaravel;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use Illuminate\Support\Str;

class MarkdownConversion
{
    private const BOLD_TAGS = ['b', 'strong'];

    private const ITALIC_TAGS = ['i', 'em'];

    private const STRIKETHROUGH_TAGS = ['s'];

    private const LIST_BULLET = '/^(-|\d+\. )/';

    private const LIST_INDENT = '  ';

    private const PROTOCOL_REGEXP = '/^([a-zA-Z][a-zA-Z\d+\-.]*):/';

    public static function nodeToMarkdown(DOMDocument $node): string
    {
        return trim(static::markdownForNode($node));
    }

    private static function markdownForNode(DOMNode $node): mixed
    {
        $method = static::markdownMethodForNode($node);

        if (method_exists(static::class, $method)) {
            return call_user_func([static::class, $method], $node);
        }

        if ($node instanceof DOMText) {
            return static::markdownForTextNode($node);
        }

        return static::markdownForNodeChildren($node);
    }

    private static function markdownMethodForNode(DOMNode $node): string
    {
        return sprintf('markdownFor%sNode', (string) Str::of($node->nodeName)->studly());
    }

    private static function markdownForNodeChildren(DOMNode $node): mixed
    {
        $values = [];

        foreach ($node->childNodes as $child) {
            $values[] = static::markdownForNode($child);
        }

        return static::joinChildren($values);
    }

    private static function markdownForTextNode(DOMText $node): string
    {
        if (trim($node->textContent) === '' && ! static::significantWhitespace($node)) {
            return '';
        }

        return $node->textContent;
    }

    private static function markdownForStrongNode(DOMElement $node): mixed
    {
        $inner = static::markdownForNodeChildren($node);

        // Avoid double bolding if already inside a bold tag
        if (static::ancestorNamed($node, self::BOLD_TAGS, maxDepth: 4)) {
            return $inner;
        }

        return ['bold', $inner];
    }

    private static function markdownForBNode(DOMElement $node): mixed
    {
        return static::markdownForStrongNode($node);
    }

    private static function markdownForEmNode(DOMElement $node): mixed
    {
        $inner = static::markdownForNodeChildren($node);

        // Avoid double italics if already inside an italic tag
        if (static::ancestorNamed($node, self::ITALIC_TAGS, maxDepth: 4)) {
            return $inner;
        }

        return ['italic', $inner];
    }

    private static function markdownForINode(DOMElement $node): mixed
    {
        return static::markdownForEmNode($node);
    }

    private static function markdownForSNode(DOMElement $node): string
    {
        $inner = static::markdownForNodeChildren($node);

        // Avoid double strikethrough if already inside an <s> tag
        if (static::ancestorNamed($node, self::STRIKETHROUGH_TAGS, maxDepth: 4)) {
            return $inner;
        }

        return '~~'.$inner.'~~';
    }

    private static function markdownForCodeNode(DOMElement $node): string
    {
        $inner = static::markdownForNodeChildren($node);

        if ($node->parentNode instanceof DOMElement && $node->parentNode->nodeName === 'pre') {
            return $inner;
        }

        return '`'.$inner.'`';
    }

    private static function markdownForPreNode(DOMElement $node): string
    {
        $language = $node->getAttribute('data-language');

        return "```{$language}\n".trim(static::markdownForNodeChildren($node))."\n```\n\n";
    }

    private static function markdownForPNode(DOMElement $node): string
    {
        return static::markdownForNodeChildren($node)."\n\n";
    }

    private static function markdownForH1Node(DOMElement $node): string
    {
        return static::markdownForHeading($node, 1);
    }

    private static function markdownForH2Node(DOMElement $node): string
    {
        return static::markdownForHeading($node, 2);
    }

    private static function markdownForH3Node(DOMElement $node): string
    {
        return static::markdownForHeading($node, 3);
    }

    private static function markdownForH4Node(DOMElement $node): string
    {
        return static::markdownForHeading($node, 4);
    }

    private static function markdownForH5Node(DOMElement $node): string
    {
        return static::markdownForHeading($node, 5);
    }

    private static function markdownForH6Node(DOMElement $node): string
    {
        return static::markdownForHeading($node, 6);
    }

    private static function markdownForHeading(DOMElement $node, int $level): string
    {
        return str_repeat('#', $level).' '.static::markdownForNodeChildren($node)."\n\n";
    }

    private static function markdownForBlockquoteNode(DOMElement $node): string
    {
        $quoted = rtrim(static::markdownForNodeChildren($node));
        // Normalize line endings and split
        $quoted = str_replace(["\r\n", "\r"], "\n", $quoted);
        $lines = explode("\n", $quoted);
        // Filter out empty lines at the end but keep empty lines in the middle
        $filteredLines = [];
        $foundNonEmpty = false;
        // Process lines in reverse to trim trailing empties
        for ($i = count($lines) - 1; $i >= 0; $i--) {
            if (! $foundNonEmpty && trim($lines[$i]) === '') {
                continue;
            }
            $foundNonEmpty = true;
            $filteredLines[] = $lines[$i];
        }
        $lines = array_reverse($filteredLines);
        $prefixedLines = array_map(fn ($line) => '> '.$line, $lines);

        return implode("\n", $prefixedLines)."\n\n";
    }

    private static function markdownForUlNode(DOMElement $node): string
    {
        $items = static::listItemLines($node, prefix: '- ');

        return $items."\n\n";
    }

    private static function markdownForOlNode(DOMElement $node): string
    {
        $index = 0;
        $items = static::listItemLines($node, prefix: function () use (&$index) {
            return (++$index).'. ';
        });

        return $items."\n\n";
    }

    private static function markdownForANode(DOMElement $node): string
    {
        $inner = static::markdownForNodeChildren($node);
        $href = $node->getAttribute('href');

        if ($href !== '' && static::allowedHrefProtocol($href)) {
            $escapedInner = static::escapeLinkText((string) $inner);
            $escapedHref = static::escapeLinkUrl($href);

            return '['.$escapedInner.']('.$escapedHref.')';
        }

        return $inner;
    }

    private static function markdownForTrNode(DOMElement $node): string
    {
        // Check if all children are th elements (header row)
        $allTh = true;
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement && $child->nodeName !== 'th') {
                $allTh = false;
                break;
            }
        }

        if ($allTh) {
            return static::markdownForTableHeaderRow($node);
        }

        $cells = [];
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $cells[] = trim(static::stringify(static::markdownForNode($child)));
            }
        }

        return '| '.implode(' | ', $cells)." |\n";
    }

    private static function markdownForSummaryNode(DOMElement $node): string
    {
        return '**'.static::markdownForNodeChildren($node)."**\n\n";
    }

    private static function markdownForBrNode(DOMElement $node): string
    {
        return "\n";
    }

    private static function markdownForHrNode(DOMElement $node): string
    {
        return "---\n\n";
    }

    // Script and style tags should be ignored
    private static function markdownForScriptNode(DOMElement $node): string
    {
        return '';
    }

    private static function markdownForStyleNode(DOMElement $node): string
    {
        return '';
    }

    // Pass-through elements - parent handlers use child values directly
    private static function markdownForLiNode(DOMElement $node): mixed
    {
        return static::markdownForNodeChildren($node);
    }

    private static function markdownForTdNode(DOMElement $node): mixed
    {
        return static::markdownForNodeChildren($node);
    }

    private static function markdownForThNode(DOMElement $node): mixed
    {
        return static::markdownForNodeChildren($node);
    }

    private static function markdownForTheadNode(DOMElement $node): mixed
    {
        return static::markdownForNodeChildren($node);
    }

    private static function markdownForTbodyNode(DOMElement $node): mixed
    {
        return static::markdownForNodeChildren($node);
    }

    private static function markdownForTableNode(DOMElement $node): string
    {
        return static::markdownForNodeChildren($node)."\n";
    }

    private static function markdownForTableHeaderRow(DOMElement $node): string
    {
        $cells = [];
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $cells[] = trim(static::stringify(static::markdownForNode($child)));
            }
        }
        $row = '| '.implode(' | ', $cells)." |\n";
        $separator = '| '.implode(' | ', array_fill(0, count($cells), '---'))." |\n";

        return $row.$separator;
    }

    private static function allowedHrefProtocol(string $href): bool
    {
        if (preg_match(self::PROTOCOL_REGEXP, $href, $matches)) {
            $allowedProtocols = ['http', 'https', 'ftp', 'mailto', 'tel'];

            return in_array(strtolower($matches[1]), $allowedProtocols, true);
        }

        return true; // relative URL, no protocol
    }

    private static function listItemLines(DOMElement $listNode, string|callable $prefix): string
    {
        $lines = [];
        $index = 0;

        foreach ($listNode->childNodes as $child) {
            if (! $child instanceof DOMElement || $child->nodeName !== 'li') {
                continue;
            }

            $value = static::markdownForNode($child);
            $text = static::stringify($value);
            $splitLines = array_filter(explode("\n", $text), fn ($line) => trim($line) !== '');

            if ($splitLines === []) {
                continue;
            }

            $bullet = is_callable($prefix) ? $prefix($index++) : $prefix;
            $lines[] = static::formatListItem($splitLines, $bullet);
        }

        return implode("\n", $lines);
    }

    private static function formatListItem(array $lines, string $bullet): string
    {
        $first = array_shift($lines);
        $leader = preg_match(self::LIST_BULLET, $first) ? self::LIST_INDENT : $bullet;
        $result = [$leader.$first];

        foreach ($lines as $line) {
            $result[] = self::LIST_INDENT.$line;
        }

        return implode("\n", $result);
    }

    private static function joinChildren(array $childValues): mixed
    {
        $merged = [];

        foreach ($childValues as $value) {
            // Merge adjacent bold/italic runs
            if (is_array($value) && ($value[0] === 'bold' || $value[0] === 'italic')) {
                if ($merged !== [] && is_array($merged[count($merged) - 1]) && $merged[count($merged) - 1][0] === $value[0]) {
                    $merged[count($merged) - 1][1] .= $value[1];
                } else {
                    $merged[] = [$value[0], $value[1]];
                }
            } else {
                $merged[] = $value;
            }
        }

        $parts = array_map(fn ($v) => static::stringify($v), $merged);
        $result = '';

        foreach ($parts as $part) {
            // Nested block elements need an initial newline injected
            if ($result !== '' && ! str_ends_with($result, "\n") && str_ends_with($part, "\n\n")) {
                $result .= "\n";
            }
            $result .= $part;
        }

        return $result;
    }

    private static function stringify(mixed $value): string
    {
        if (is_array($value)) {
            if ($value[0] === 'bold') {
                return static::wrapEmphasis($value[1], '**');
            }
            if ($value[0] === 'italic') {
                return static::wrapEmphasis($value[1], '*');
            }

            return implode('', $value);
        }

        return (string) $value;
    }

    private static function wrapEmphasis(string $text, string $marker): string
    {
        preg_match('/^(\s*)/', $text, $leadingMatch);
        preg_match('/(\s*)$/', $text, $trailingMatch);
        $leading = $leadingMatch[1] ?? '';
        $trailing = $trailingMatch[1] ?? '';
        $inner = trim($text);

        return $leading.$marker.$inner.$marker.$trailing;
    }

    private const BLOCK_ELEMENTS = [
        'address', 'article', 'aside', 'blockquote', 'details', 'dialog',
        'dd', 'div', 'dl', 'dt', 'fieldset', 'figcaption', 'figure',
        'footer', 'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header',
        'hgroup', 'hr', 'li', 'main', 'nav', 'ol', 'p', 'pre', 'section',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'td', 'th', 'ul',
    ];

    private static function significantWhitespace(DOMText $node): bool
    {
        $prev = $node->previousSibling;
        $next = $node->nextSibling;

        if ($prev === null || $next === null) {
            return false;
        }

        // Whitespace is significant if at least one sibling is inline-level
        // (either a text node or an inline element like <code>, <strong>, etc.)
        return static::isInlineNode($prev) || static::isInlineNode($next);
    }

    private static function isInlineNode(DOMNode $node): bool
    {
        if ($node instanceof DOMText) {
            return true;
        }

        if ($node instanceof DOMElement) {
            return ! in_array($node->nodeName, self::BLOCK_ELEMENTS, true);
        }

        return false;
    }

    public static function escapeLinkText(string $text): string
    {
        return str_replace(['[', ']'], ['\\[', '\\]'], $text);
    }

    public static function escapeLinkUrl(string $url): string
    {
        return str_replace(['(', ')'], ['\\(', '\\)'], $url);
    }

    private static function ancestorNamed(DOMElement $node, array $names, int $maxDepth): bool
    {
        $current = $node->parentNode;
        $depth = 0;

        while ($current instanceof DOMElement && $depth < $maxDepth) {
            if (in_array($current->nodeName, $names, true)) {
                return true;
            }
            $current = $current->parentNode;
            $depth++;
        }

        return false;
    }
}
