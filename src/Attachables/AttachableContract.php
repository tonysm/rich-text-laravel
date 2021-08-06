<?php

namespace Tonysm\RichTextLaravel\Attachables;

interface AttachableContract
{
    public function richTextPreviewable(): bool;

    public function richTextFilename(): string;

    public function richTextContentType(): string;

    public function richTextFilesize(): ?int;

    public function richTextMetadata(string $key);

    public function richTextRender(): string;

    public function richTextSgid(): string;

    public function toRichTextAttributes(array $attributes): array;
}
