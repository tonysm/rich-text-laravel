<?php

namespace Workbench\App\Models\User;

use Tonysm\RichTextLaravel\Attachables\Attachable;

trait Mentionee
{
    use Attachable;

    public function richTextRender(array $options = []): string
    {
        return view('mentions.partials.user', [
            'user' => $this,
        ])->render();
    }

    public function richTextAsPlainText()
    {
        return e($this->name);
    }
}
