<?php

namespace Tonysm\RichTextLaravel;

use Illuminate\Pipeline\Pipeline;

class ParseAttachments
{
    public function __invoke(string $content): string
    {
        return (new Pipeline(app()))
            ->send($content)
            ->through([
                Actions\ExtractAttachments::class,
            ])
            ->thenReturn();
    }
}
