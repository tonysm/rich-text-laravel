<?php

namespace Tonysm\RichTextLaravel\Editor;

use Tonysm\RichTextLaravel\Fragment;

interface Editor
{
    /**
     * Transforms the given fragment into its canonical form for storage.
     */
    public function asCanonical(Fragment $fragment): Fragment;

    /**
     * Transforms the given fragment into its editable form for editing.
     */
    public function asEditable(Fragment $fragment): Fragment;
}
