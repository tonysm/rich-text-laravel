<?php

namespace Tonysm\RichTextLaravel\View\Components;

use Illuminate\View\Component;

class TrixStyles extends Component
{
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('rich-text-laravel::components.core-styles');
    }
}
