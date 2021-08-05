<?php

namespace Tonysm\RichTextLaravel\Actions;

use DOMDocument;
use DOMXPath;
use Tonysm\RichTextLaravel\TrixContent;

class ExtractAttachables
{
    public function __construct(private DOMDocument $document)
    {
    }

    public function each(callable $each)
    {
        $xpath = new DOMXPath($this->document);

        $attachables = $xpath->query(TrixContent::ATTACHABLE_SELECTOR);

        if ($attachables !== false) {
            foreach ($attachables as $attachable) {
                $each($attachable);
            }
        }
    }
}
