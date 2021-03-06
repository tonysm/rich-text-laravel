<?php

namespace Tonysm\RichTextLaravel\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use Tonysm\RichTextLaravel\Attachables\Attachable;
use Tonysm\RichTextLaravel\Attachables\AttachableContract;

class User extends Model implements AttachableContract
{
    use Attachable;

    protected $guarded = [];

    public function richTextRender($content = null, array $options = []): string
    {
        return view('user_test', array_merge($options, [
            'user' => $this,
        ]))->render();
    }
}
