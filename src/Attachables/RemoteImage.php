<?php

namespace Tonysm\RichTextLaravel\Attachables;

use DOMElement;

class RemoteImage implements AttachableContract
{
    use Attachable;

    public $url;
    public $contentType;
    public $width;
    public $height;

    public DOMElement $node;

    public static function fromNode(DOMElement $node): ?AttachableContract
    {
        if ($node->hasAttribute('url') && str_starts_with($node->getAttribute('content-type'), 'image')) {
            return new static(static::attributesFromNode($node), $node);
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

    public function __construct(array $attributes, DOMElement $node)
    {
        $this->url = $attributes['url'];
        $this->contentType = $attributes['content_type'];
        $this->width = $attributes['width'];
        $this->height = $attributes['height'];

        $this->node = $node;
    }

    public function richTextRender(): string
    {
        return view('rich-text-laravel::attachables._remote_image', [
            'remoteImage' => $this,
        ])->render();
    }

    public function nodeCaption(): ?string
    {
        return $this->node->getAttribute('caption');
    }

    public function plainTextRender(): string
    {
        $caption = $this->nodeCaption();

        return sprintf('[%s]', $caption ?: 'Image');
    }
}
