<?php

namespace Workbench\App\Html;

use Illuminate\Support\HtmlString;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;

class Sanitizer
{
    public function __construct(private HtmlSanitizer $sanitizer)
    {
        //
    }

    public function sanitize(string $html, string $element = 'body'): HtmlString
    {
        return new HtmlString($this->sanitizer->sanitizeFor($element, $html));
    }
}
