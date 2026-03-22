<?php

namespace Tonysm\RichTextLaravel\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class RichTextAttributes
{
    /**
     * Create a new attribute instance.
     *
     * @param  array<int, string|array>  $columns
     */
    public function __construct(public array $columns) {}
}
