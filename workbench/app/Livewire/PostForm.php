<?php

namespace Workbench\App\Livewire;

use Livewire\Attributes\Validate;
use Livewire\Form;

class PostForm extends Form
{
    #[Validate('required')]
    public $title = '';

    #[Validate('required')]
    public $body = '';
}
