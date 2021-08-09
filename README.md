<p align="center" style="margin-top: 2rem; margin-bottom: 2rem;"><img src="/art/rich-text-laravel-logo.svg" alt="Logo Rich Text Laravel" /></p>

<p align="center">
    <a href="https://github.com/tonysm/rich-text-laravel/workflows/run-tests/badge.svg">
        <img src="https://img.shields.io/github/workflow/status/tonysm/rich-text-laravel/run-tests?label=tests" />
    </a>
    <a href="https://packagist.org/packages/tonysm/rich-text-laravel">
        <img src="https://img.shields.io/packagist/dt/tonysm/rich-text-laravel" alt="Total Downloads">
    </a>
    <a href="https://packagist.org/packages/tonysm/rich-text-laravel">
        <img src="https://img.shields.io/github/license/tonysm/rich-text-laravel" alt="License">
    </a>
</p>

Integrates the Trix Editor with Laravel. Inspired by the Action Text gem from Rails.

**🚧 This is still in development. It's not production-ready yet.**

## Installation

You can install the package via composer:

```bash
composer require tonysm/rich-text-laravel
```

## Usage

We're going to extract attachments before saving the rich text field (which uses Trix) in the database. We replace the attachment with `rich-text-attachment` tag with an `sgid`. When rendering that rich content again, we can render the attachables. This works for Remote URLs and for any Attachable record (more on that later).

The way this works is that we're going to add cast to any Rich Text field on any model, like so:

```php
use Tonysm\RichTextLaravel\Casts\AsRichTextContent;

class Post extends Model
{
    protected $casts = [
        'content' => AsRichTextContent::class,
    ];
}
```

Then caster will parse the HTML content and minify it for storage. Essentially, it will convert this image attachment:

```php
$post->update([
    'content' => <<<HTML
    <div>
        <h1>Hello World</h1>
        <figure data-trix-attachment='{
            "url": "http://example.com/blue.jpg",
            "width": 300,
            "height": 150,
            "contentType": "image/jpeg",
            "caption": "Something cool",
            "filename":"blue.png",
            "filesize":1168
        }'>
            <img src="http://example.com/blue.jpg" width="300" height="150" />
            <caption>
                Something cool
            </caption>
        </figure>
    </div>
    HTML,
])
```

to this minified version:

```html
<div>
    <h1>Hello World</h1>
    <rich-text-attachment content-type="image/jpeg" filename="blue.png" filesize="1168" height="300" href="http://example.com/blue.jpg" url="http://example.com/blue.jpg" width="300" caption="testing this caption" presentation="gallery"></rich-text-attachment>
</div>
```

And when it renders it again, it will re-render the remote image again inside the `rich-text-attachment` tag. You can render the content for *viewing* by simply echoing out the output, something like this:

```blade
{!! $post->content !!}
```

*Note*: since the HTML output is NOT escaped, make sure you sanitize it before rendering. You can use something like the [mews/purifier](https://github.com/mewebstudio/Purifier) package, which would look like this:

```blade
{!! clean($post->content) !!}
```

When feeding the Trix editor again, you need to do it differently:

```blade
<x-trix-editor :value="$post->content->toTrixHtml()" />
```

Rendering for the editor is a bit different, so it has to be like that.

### Attaching Models

You can have any model on your application as attachable inside a Trix rich text field. To do that, you need to implement the `AttachableContract` and use the `Attachable` trait in a model. Besides that, you also have to implement a `richTextRender(array $options): string` where you can tell the package how to render that model inside Trix:

```php
use Tonysm\RichTextLaravel\Attachables\AttachableContract;
use Tonysm\RichTextLaravel\Attachables\Attachable;

class User extends Model implements AttachableContract
{
    use Attachable;

    public function richTextRender(array $options = []): string
    {
        return view('users._mention', [
            'user' => $this,
        ])->render();
    }
}
```

Then inside that `users._mention` Blade template you have full control over the HTML for this attachable field.

TODO: document the options array.

### Getting Attachables

You can retrieve all the attachments of a rich content field using the `attachments()` method from the content instance:

```php
$post->content->attachments()
```

This will return a collection of all the attachments (anything that is an attachable, really, so images and users, for instance - if you want only attachments of a specific attachable you can use the filter method on the collection).

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Tony Messias](https://github.com/tonysm)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
