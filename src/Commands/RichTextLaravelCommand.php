<?php

namespace Tonysm\RichTextLaravel\Commands;

use Illuminate\Console\Command;

class RichTextLaravelCommand extends Command
{
    public $signature = 'rich-text-laravel';

    public $description = 'My command';

    public function handle()
    {
        $this->comment('All done');
    }
}
