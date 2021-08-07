<?php

namespace Tonysm\RichTextLaravel\Actions;

use Illuminate\Pipeline\Pipeline;
use Tonysm\RichTextLaravel\Actions\Rendering\ConvertToPlainText;
use Tonysm\RichTextLaravel\Actions\Rendering\InjectAttachmentContent;
use Tonysm\RichTextLaravel\Attachment;

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
                (new InjectAttachmentContent($this->plainText)),
                $this->plainText ? ConvertToPlainText::class : null,
            ]))
            ->thenReturn();
    }

    public function renderContent(Attachment $attachment): ?string
    {
        if (! $this->withContents) {
            return null;
        }

        return match ($this->plainText) {
            true => $attachment->richTextRender(),
            false => $attachment->toPlainText(),
        };
    }
}
