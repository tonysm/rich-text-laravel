<?php

namespace Tonysm\RichTextLaravel\Attachables;

use DOMElement;
use Illuminate\Support\Str;

class RemoteFile implements AttachableContract
{
    public $url;

    public $contentType;

    public $filename;

    public $filesize;

    public $caption;

    public static function fromNode(DOMElement $node): ?static
    {
        if ($node->hasAttribute('url') && in_array($node->getAttribute('content-type'), config('rich-text-laravel.supported_files_content_types'))) {
            return new static(static::attributesFromNode($node));
        }

        return null;
    }

    private static function attributesFromNode(DOMElement $node): array
    {
        return [
            'url' => $node->getAttribute('url'),
            'content_type' => $node->getAttribute('content-type'),
            'filename' => $node->getAttribute('filename'),
            'filesize' => $node->getAttribute('filesize'),
            'caption' => $node->hasAttribute('caption') ? $node->getAttribute('caption') : null,
        ];
    }

    public function __construct(array $attributes)
    {
        $this->url = $attributes['url'];
        $this->contentType = $attributes['content_type'];
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
        ];
    }

    public function equalsToAttachable(AttachableContract $attachable): bool
    {
        return $attachable instanceof static
            && $attachable->richTextMetadata() == $this->richTextMetadata();
    }

    public function richTextRender(array $options = []): string
    {
        return view('rich-text-laravel::attachables._remote_file', [
            'remoteFile' => $this,
        ])->render();
    }

    public function richTextAsPlainText($caption = null): string
    {
        return __(sprintf('[%s]', $caption ?: $this->filename ?: 'File'));
    }

    public function extension(): string
    {
        return (string) Str::of($this->url ?: '.unkown')->afterLast('.');
    }

    public function filesizeForHumans(): string
    {
        if ($this->filesize >= 1 << 30) {
            return number_format($this->filesize / (1 << 30), 2).' GB';
        }

        if ($this->filesize >= 1 << 20) {
            return number_format($this->filesize / (1 << 20), 2).' MB';
        }

        if ($this->filesize >= 1 << 10) {
            return number_format($this->filesize / (1 << 10), 2).' KB';
        }

        return number_format($this->filesize).' Bytes';
    }
}
