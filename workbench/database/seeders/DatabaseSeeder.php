<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Js;
use Workbench\App\Models\User;
use Workbench\Database\Factories\PostFactory;
use Workbench\Database\Factories\UserFactory;

class DatabaseSeeder extends Seeder
{
    const FIRST_IMAGE = 'https://trix-editor.org/images/attachments/plan-01.png';

    const SECOND_IMAGE = 'https://trix-editor.org/images/attachments/plan-02.png';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        [$tony, $picard] = UserFactory::new()->times(5)->sequence(
            ['name' => 'Tony Messias'],
            ['name' => 'Jean-Luc Picard'],
            ['name' => 'James Kirk'],
            ['name' => 'Spock'],
            ['name' => 'Kathryn Janeway'],
        )->create();

        $trixAttributes = fn (User $mention) => e(Js::encode($mention->toRichTextAttributes([
            'content' => $mention->richTextRender(),
        ])));

        $encodeImage = fn (string $imageUrl) => e($imageUrl);

        PostFactory::new()->create([
            'title' => 'Rich Text Laravel & Trix',
            'body' => <<<HTML
            <div>
            <strong><em>Hey, folks<br><br></em></strong>This is an example of using the <a href="https://github.com/tonysm/rich-text-laravel">Rich Text Laravel</a> package, which serves as a backend integration for the <a href="https://trix-editor.org/">Trix</a> editor.<br><br>You can at-mention folks like <figure data-trix-attachment="{$trixAttributes($tony)}"></figure> or <figure data-trix-attachment="{$trixAttributes($picard)}"></figure>, and then scan the document later to extract them.<br><br>It supports code too:</div>
            <div><pre>console.log('hello')</pre></div>
            <div>Now a quote:<br><br></div>
            <blockquote>Hello World! - Data</blockquote>
            <div><br>And lists:</div>
            <ul><li>First Item</li><li>Second Item</li></ul>
            <div><br>And ordered lists:</div>
            <ol><li>First Item</li><li>Second Item</li></ol>
            <div><h1>Image Support</h1></div>
            <div>It also supports image uploads:</div>
            <div><figure data-trix-attachment='{"contentType":"image\/jpeg","url":"{$encodeImage(self::FIRST_IMAGE)}","href":"{$encodeImage(self::FIRST_IMAGE)}","filename":"first-image.jpg","filesize":47665,"width":880,"height":660}' data-trix-attributes='{"presentation":"gallery","caption":"First Image"}'></figure><figure data-trix-attachment='{"contentType":"image\/jpeg","url":"{$encodeImage(self::SECOND_IMAGE)}","href":"{$encodeImage(self::SECOND_IMAGE)}","filename":"second-image.jpg","filesize":25230,"width":325,"height":396}' data-trix-attributes='{"presentation":"gallery","caption":"Second Image"}'></figure></div>
            <div>
            How cool is that?<br><br>
            Cheers!
            </div>
            HTML,
        ]);
    }
}
