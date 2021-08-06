<?php

namespace Tonysm\RichTextLaravel\Attachables;

use Tonysm\RichTextLaravel\GlobalId;

trait Attachable
{
    public function richTextSgid(): string
    {
        return (new GlobalId($this))->toStorage();
    }

    public function richTextPreviewable(): bool
    {
        return false;
    }

    public function richTextFilename(): string
    {
        return '';
    }

    public function richTextContentType(): string
    {
        if (property_exists($this, 'richTextContentType')) {
            return $this->richTextContentType;
        }

        return "application/octet-stream";
    }

    public function richTextFilesize(): ?int
    {
        return null;
    }

    public function richTextMetadata(string $key)
    {
        return null;
    }

    public function toRichTextAttributes(array $attributes = []): array
    {
        return array_replace($attributes, [
            'sgid' => $this->richTextSgid(),
            'content_type' => $this->richTextContentType(),
            'previewable' => $this->richTextPreviewable(),
            'filename' => $this->richTextFilename(),
            'filesize' => $this->richTextFilesize(),
            'width' => $this->richTextMetadata('width'),
            'height' => $this->richTextMetadata('height'),
        ]);
    }
}
