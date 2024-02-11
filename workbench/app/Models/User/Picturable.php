<?php

namespace Workbench\App\Models\User;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait Picturable
{
    protected function pictureUrl(): Attribute
    {
        return Attribute::get(function () {
            $name = trim(collect(explode(' ', $this->name))->map(function ($segment) {
                return mb_substr($segment, 0, 1);
            })->join(' '));

            return 'https://ui-avatars.com/api/?name='.urlencode($name).'&color=7F9CF5&background=EBF4FF';
        });
    }
}
