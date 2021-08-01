<?php

namespace Tonysm\RichTextLaravel\Commands;

use Illuminate\Console\Command;

class RichTextLaravelInstallCommand extends Command
{
    public $signature = 'richtext:install';

    public $description = 'Installs the package.';

    public function handle()
    {
        $this->comment('All done');
    }
}
