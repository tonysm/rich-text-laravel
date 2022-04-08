<?php

namespace Tonysm\RichTextLaravel;

use Livewire\Livewire;
use Tonysm\RichTextLaravel\Livewire\WithRichTexts;
use Tonysm\RichTextLaravel\Models\Traits\HasRichText;

class LivewireSupportsRichText
{
    public static function init()
    {
        new static;
    }

    public function __construct()
    {
        if (! class_exists(Livewire::class)) {
            return;
        }

        Livewire::listen('property.dehydrate', function ($property, $value, $component, $response) {
            $uses = array_flip(class_uses_recursive($component));

            if (! in_array(WithRichTexts::class, $uses)) {
                return;
            }

            $this->dehydratePropertyFromWithRichText($value);
        });
    }

    protected function dehydratePropertyFromWithRichText($value)
    {
        if (! is_object($value)) {
            return;
        }

        $uses = array_flip(class_uses_recursive($value));

        if (! in_array(HasRichText::class, $uses)) {
            return;
        }

        $value->unsetRichTextRelationshipsForLivewireDehydration();
    }
}
