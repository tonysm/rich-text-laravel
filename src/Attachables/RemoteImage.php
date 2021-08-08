<?php

namespace Tonysm\RichTextLaravel\Attachables;

use DOMElement;
use Illuminate\Support\Str;

class RemoteImage implements AttachableContract
{
    use Attachable;

    public $url;
    public $contentType;
    public $width;
    public $height;

    public static function fromNode(DOMElement $node): ?static
    {
        if ($node->hasAttribute('url') && str_starts_with($node->getAttribute('content-type'), 'image')) {
            return new static(static::attributesFromNode($node));
        }

        return null;
    }

    private static function attributesFromNode(DOMElement $node): array
    {
        return [
            'url' => $node->getAttribute('url'),
            'content_type' => $node->getAttribute('content-type'),
            'width' => $node->getAttribute('width'),
            'height' => $node->getAttribute('height'),
        ];
    }

    public function __construct(array $attributes)
    {
        $this->url = $attributes['url'];
        $this->contentType = $attributes['content_type'];
        $this->width = $attributes['width'];
        $this->height = $attributes['height'];
    }

    public function richTextRender($content = null, array $options = []): string
    {
        return view('rich-text-laravel::attachables._remote_image', array_merge($options, [
            'remoteImage' => $this,
        ]))->render();
    }

    public function richTextAsPlainText($caption = null)
    {
        return sprintf("[%s]", $caption ?: 'Image');
    }

    public function extension(): string
    {
        return (string) Str::of($this->url ?: '.unkown')->afterLast('.');
    }
}
