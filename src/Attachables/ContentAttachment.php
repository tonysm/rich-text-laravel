<?php

namespace Tonysm\RichTextLaravel\Attachables;

use DOMElement;
use Illuminate\Support\Str;

class ContentAttachment implements AttachableContract
{
    const NAME_PATTERN = '/vnd\.richtextlaravel\.(.+)\.html/';

    public static $validNames = ['horizontal-rule'];

    public static function fromNode(DOMElement $node): ?static
    {
        if (! $node->hasAttribute('content-type')) {
            return null;
        }

        if (! preg_match(static::NAME_PATTERN, $node->getAttribute('content-type'), $matches)) {
            return null;
        }

        $name = $matches[1];

        if (! $name || ! static::validName($name)) {
            return null;
        }

        return new static($name);
    }

    private static function validName(string $name): bool
    {
        return in_array($name, static::$validNames);
    }

    public function __construct(public $name)
    {
    }

    public function toRichTextAttributes(array $attributes): array
    {
        return [
            'content' => $this->renderTrixContentAttachment(),
        ];
    }

    public function equalsToAttachable(AttachableContract $attachable): bool
    {
        return $attachable instanceof static
            && $attachable->name === $this->name;
    }

    public function richTextAsPlainText(): string
    {
        if ($this->name === 'horizontal-rule') {
            return ' ┄ ';
        }

        return ' ';
    }

    public function richTextRender(array $options = []): string
    {
        return view('rich-text-laravel::contents._content', [
            'content' => $this,
            'options' => $options,
        ])->render();
    }

    public function renderTrixContentAttachment(array $options = []): string
    {
        return view('rich-text-laravel::contents._'.Str::of($this->name)->studly()->snake('_'), [
            'content' => $this,
            'options' => $options,
        ])->render();
    }
}
