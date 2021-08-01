<?php

namespace Tonysm\RichTextLaravel;

use Illuminate\Queue\SerializesModels;

class GlobalId
{
    use SerializesModels;

    public function __construct(public $record)
    {
    }
}
