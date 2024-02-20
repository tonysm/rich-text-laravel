<?php

namespace Workbench\App\Models;

class EncryptedMessage extends Message
{
    protected $richTextAttributes = [
        'content' => ['encrypted' => true],
    ];
}
