<?php

namespace Tonysm\RichTextLaravel\Editor;

use Tonysm\RichTextLaravel\Fragment;

class LexxyEditor implements Editor
{
    public function asCanonical(Fragment $fragment): Fragment
    {
        return $fragment;
    }

    public function asEditable(Fragment $fragment): Fragment
    {
        return $fragment;
    }
}
