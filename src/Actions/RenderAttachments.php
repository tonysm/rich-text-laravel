<?php

namespace Tonysm\RichTextLaravel\Actions;

use Illuminate\Pipeline\Pipeline;
use Tonysm\RichTextLaravel\Actions\Rendering\ConvertToPlainText;
use Tonysm\RichTextLaravel\Actions\Rendering\InjectAttachmentContent;

class RenderAttachments
{
    public function __construct(private $plainText = false, private $withContents = false)
    {
    }

    public function __invoke(string $content): string
    {
        if (! $content) {
            return $content;
        }

        return (new Pipeline(app()))
            ->send($content)
            ->through(array_filter([
                $this->withContents ? (new InjectAttachmentContent($this->plainText)) : null,
                $this->plainText ? ConvertToPlainText::class : null,
            ]))
            ->thenReturn();
    }
}
