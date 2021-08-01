<?php

namespace Tonysm\RichTextLaravel;

use Illuminate\Pipeline\Pipeline;

class ParseAttachables
{
    public function __invoke(string $rawContent, bool $withAttachablesContent = false): string
    {
        return (new Pipeline(app()))
            ->send($rawContent)
            ->through(array_filter([
                Actions\ExtractAttachments::class,
                $withAttachablesContent ? Actions\RenderAttachables::class : null,
            ]))
            ->thenReturn();
    }
}
