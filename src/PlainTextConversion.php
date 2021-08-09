<?php

namespace Tonysm\RichTextLaravel;

use DOMDocument;
use DOMNode;
use DOMText;
use Illuminate\Support\Str;

class PlainTextConversion
{
    public static function nodeToPlainText(DOMDocument $node)
    {
        return static::removeTrailingNewLines(static::plainTextForNode($node));
    }

    public static function plainTextForNode(DOMNode $node)
    {
        $method = static::plainTextMethodForNode($node);

        if (method_exists(static::class, $method)) {
            return call_user_func([static::class, $method], $node);
        }

        if ($node instanceof DOMText) {
            return static::plainTextForTextNode($node);
        }

        return static::plainTextForNodeChildren($node);
    }

    public static function plainTextMethodForNode(DOMNode $node): string
    {
        return sprintf('plainTextFor%sNode', (string) Str::of($node->nodeName)->studly());
    }

    public static function plainTextForNodeChildren(DOMNode $node): string
    {
        $texts = [];
        $index = 0;

        foreach ($node->childNodes as $child) {
            /** @psalm-suppress TooManyArguments */
            $texts[] = static::plainTextForNode($child, $index++);
        }

        return implode('', $texts);
    }

    public static function plainTextForBlock(DOMNode $node)
    {
        return sprintf("%s\n\n", static::removeTrailingNewLines(static::plainTextForNodeChildren($node)));
    }

    public static function plainTextForH1Node(DOMNode $node)
    {
        return static::plainTextForBlock($node);
    }

    public static function plainTextForPNode(DOMNode $node)
    {
        return static::plainTextForBlock($node);
    }

    public static function plainTextForUlNode(DOMNode $node)
    {
        return static::plainTextForBlock($node);
    }

    public static function plainTextForOlNode(DOMNode $node)
    {
        return static::plainTextForBlock($node);
    }

    public static function plainTextForBrNode(DOMNode $node)
    {
        return "\n";
    }

    public static function plainTextForTextNode(DOMText $node)
    {
        return static::removeTrailingNewLines($node->ownerDocument->saveHTML($node));
    }

    public static function plainTextForDivNode(DOMNode $node)
    {
        return sprintf("%s\n", static::removeTrailingNewLines(static::plainTextForNodeChildren($node)));
    }

    public static function plainTextForFigcaptionNode(DOMNode $node)
    {
        return sprintf("[%s]", static::removeTrailingNewLines(static::plainTextForNodeChildren($node)));
    }

    public static function plainTextForBlockquoteNode(DOMNode $node)
    {
        $text = static::plainTextForBlock($node);

        return preg_replace('/\A(\s*)(.+?)(\s*)\Z/m', '\1“\2”\3', $text);
    }

    public static function plainTextForLiNode(DOMNode $node, $index = 0)
    {
        $bullet = static::bulletForLiNode($node, $index);

        $text = static::removeTrailingNewLines(static::plainTextForNodeChildren($node));

        return sprintf("%s %s\n", $bullet, $text);
    }

    public static function bulletForLiNode(DOMNode $node, $index)
    {
        if ($node->parentNode->nodeName === 'ol') {
            return sprintf("%s.", $index + 1);
        }

        return "•";
    }

    private static function removeTrailingNewLines(string $text): string
    {
        return trim($text, "\n\r");
    }
}
