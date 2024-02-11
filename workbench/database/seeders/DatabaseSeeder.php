<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Workbench\Database\Factories\PostFactory;
use Workbench\Database\Factories\UserFactory;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserFactory::new()->times(5)->sequence(
            ['name' => 'Tony Messias'],
            ['name' => 'Jean-Luc Picard'],
            ['name' => 'James Kirk'],
            ['name' => 'Spock'],
            ['name' => 'Kathryn Janeway'],
        )->create();

        PostFactory::new()->times(3)->create();
    }
}
