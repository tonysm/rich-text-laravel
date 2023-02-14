<?php

namespace Tonysm\RichTextLaravel\Attachables;

use DOMElement;
use Illuminate\Support\Str;

class RemoteImage implements AttachableContract
{
    public $url;

    public $contentType;

    public $width;

    public $height;

    public $filename;

    public $filesize;

    public $caption;

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
            'filename' => $node->getAttribute('filename'),
            'filesize' => $node->getAttribute('filesize'),
            'caption' => $node->hasAttribute('caption') ? $node->getAttribute('caption') : null,
        ];
    }

    public function __construct(array $attributes)
    {
        $this->url = $attributes['url'];
        $this->contentType = $attributes['content_type'];
        $this->width = $attributes['width'];
        $this->height = $attributes['height'];
        $this->filename = $attributes['filename'];
        $this->filesize = $attributes['filesize'];
        $this->caption = $attributes['caption'];
    }

    public function richTextContentType(): string
    {
        return $this->contentType;
    }

    public function richTextMetadata(?string $key = null)
    {
        $data = [
            'width' => $this->width,
            'height' => $this->height,
            'contentType' => $this->contentType,
            'url' => $this->url,
            'filename' => $this->filename,
            'filesize' => $this->filesize,
            'caption' => $this->caption,
        ];

        if (! $key) {
            return $data;
        }

        return $data[$key] ?? null;
    }

    public function richTextSgid(): string
    {
        return '';
    }

    public function toRichTextAttributes(array $attributes): array
    {
        return [
            'content_type' => $this->richTextContentType(),
            'filename' => $this->filename,
            'filesize' => $this->filesize,
            'width' => $this->width,
            'height' => $this->height,
        ];
    }

    public function equalsToAttachable(AttachableContract $attachable): bool
    {
        return $attachable instanceof static
            && $attachable->richTextMetadata() == $this->richTextMetadata();
    }

    public function richTextRender(array $options = []): string
    {
        return view('rich-text-laravel::attachables._remote_image', [
            'remoteImage' => $this,
        ])->render();
    }

    public function richTextAsPlainText($caption = null): string
    {
        return sprintf('[%s]', $caption ?: 'Image');
    }

    public function extension(): string
    {
        return (string) Str::of($this->url ?: '.unkown')->afterLast('.');
    }
}
