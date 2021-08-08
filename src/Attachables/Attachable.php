<?php

namespace Tonysm\RichTextLaravel\Attachables;

use Tonysm\RichTextLaravel\GlobalId;

trait Attachable
{
    public function richTextSgid(): string
    {
        return (new GlobalId($this))->toString();
    }
}
