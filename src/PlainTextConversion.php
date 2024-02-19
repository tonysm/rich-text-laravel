<?php

namespace Tonysm\RichTextLaravel;

use DOMDocument;
use DOMNode;
use DOMText;
use Illuminate\Support\Str;

class PlainTextConversion
{
    public static function nodeToPlainText(DOMDocument $node): string
    {
        return static::removeTrailingNewLines(static::plainTextForNode($node));
    }

    private static function plainTextForNode(DOMNode $node, $index = 0)
    {
        $method = static::plainTextMethodForNode($node);

        if (method_exists(static::class, $method)) {
            return call_user_func([static::class, $method], $node, $index);
        }

        if ($node instanceof DOMText) {
            return static::plainTextForTextNode($node);
        }

        return static::plainTextForNodeChildren($node);
    }

    private static function plainTextMethodForNode(DOMNode $node): string
    {
        return sprintf('plainTextFor%sNode', (string) Str::of($node->nodeName)->studly());
    }

    private static function plainTextForNodeChildren(DOMNode $node): string
    {
        $texts = [];
        $index = 0;

        foreach ($node->childNodes as $child) {
            /** @psalm-suppress TooManyArguments */
            $texts[] = static::plainTextForNode($child, $index++);
        }

        return implode('', $texts);
    }

    private static function plainTextForBlock(DOMNode $node): string
    {
        return sprintf("%s\n\n", static::removeTrailingNewLines(static::plainTextForNodeChildren($node)));
    }

    private static function plainTextForH1Node(DOMNode $node)
    {
        return static::plainTextForBlock($node);
    }

    private static function plainTextForH2Node(DOMNode $node)
    {
        return static::plainTextForBlock($node);
    }

    private static function plainTextForH3Node(DOMNode $node)
    {
        return static::plainTextForBlock($node);
    }

    private static function plainTextForH4Node(DOMNode $node)
    {
        return static::plainTextForBlock($node);
    }

    private static function plainTextForH5Node(DOMNode $node)
    {
        return static::plainTextForBlock($node);
    }

    private static function plainTextForH6Node(DOMNode $node)
    {
        return static::plainTextForBlock($node);
    }

    private static function plainTextForPNode(DOMNode $node)
    {
        return static::plainTextForBlock($node);
    }

    private static function plainTextForUlNode(DOMNode $node)
    {
        return static::plainTextForList($node);
    }

    private static function plainTextForOlNode(DOMNode $node)
    {
        return static::plainTextForList($node);
    }

    private static function plainTextForBrNode(): string
    {
        return "\n";
    }

    private static function plainTextForList(DOMNode $node): string
    {
        return static::breakIfNestedList($node, static::plainTextForBlock($node));
    }

    private static function plainTextForTextNode(DOMText $node): string
    {
        return static::removeTrailingNewLines($node->ownerDocument->saveHTML($node));
    }

    private static function plainTextForDivNode(DOMNode $node): string
    {
        return sprintf("%s\n\n", static::removeTrailingNewLines(static::plainTextForNodeChildren($node)));
    }

    private static function plainTextForFigcaptionNode(DOMNode $node): string
    {
        return sprintf('[%s]', static::removeTrailingNewLines(static::plainTextForNodeChildren($node)));
    }

    private static function plainTextForBlockquoteNode(DOMNode $node): ?string
    {
        $text = static::plainTextForBlock($node);

        return preg_replace('/\A(\s*)(.+?)(\s*)\Z/m', '\1“\2”\3', $text);
    }

    private static function plainTextForLiNode(DOMNode $node, $index = 0): string
    {
        $bullet = static::bulletForLiNode($node, $index);
        $text = static::removeTrailingNewLines(static::plainTextForNodeChildren($node));
        $indentation = static::indentationForLiNode($node);

        return sprintf("%s%s %s\n", $indentation, $bullet, $text);
    }

    private static function plainTextForPreNode(DOMNode $node, $index = 0): string
    {
        return static::plainTextForBlock($node);
    }

    private static function bulletForLiNode(DOMNode $node, $index): string
    {
        if ($node->parentNode->nodeName === 'ol') {
            return sprintf('%s.', $index + 1);
        }

        return '•';
    }

    private static function breakIfNestedList(DOMNode $node, string $text): string
    {
        if (static::listNodeDepthForNode($node) > 0) {
            return "\n{$text}";
        }

        return $text;
    }

    private static function indentationForLiNode(DOMNode $node): string
    {
        $depth = static::listNodeDepthForNode($node);

        if ($depth > 1) {
            return str_repeat('  ', $depth - 1);
        }

        return '';
    }

    private static function listNodeDepthForNode(DOMNode $node): int
    {
        preg_match_all('#/[uo]l/#', $node->getNodePath(), $matches);

        if (! isset($matches[0])) {
            return 1;
        }

        return count($matches[0]);
    }

    private static function removeTrailingNewLines(string $text): string
    {
        return trim($text, "\n\r");
    }
}
