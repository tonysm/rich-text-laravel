<?php

namespace Tonysm\RichTextLaravel\Attachables;

interface AttachableContract
{
    public function toRichTextAttributes(array $attributes): array;

    public function equalsToAttachable(AttachableContract $attachable): bool;

    public function richTextRender(array $options = []): string;
}
