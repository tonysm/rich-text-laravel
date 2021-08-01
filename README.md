# Rich Text Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tonysm/rich-text-laravel.svg?style=flat-square)](https://packagist.org/packages/tonysm/rich-text-laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/tonysm/rich-text-laravel/run-tests?label=tests)](https://github.com/tonysm/rich-text-laravel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/tonysm/rich-text-laravel/Check%20&%20fix%20styling?label=code%20style)](https://github.com/tonysm/rich-text-laravel/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/tonysm/rich-text-laravel.svg?style=flat-square)](https://packagist.org/packages/tonysm/rich-text-laravel)

Integrates the Trix Editor with Laravel. Inspired by the Action Text gem from Rails.

## Installation

You can install the package via composer:

```bash
composer require tonysm/rich-text-laravel
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --provider="Tonysm\RichTextLaravel\RichTextLaravelServiceProvider" --tag="rich-text-laravel-migrations"
php artisan migrate
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Tonysm\RichTextLaravel\RichTextLaravelServiceProvider" --tag="rich-text-laravel-config"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

We're going to extract attachments before saving the rich text field (which uses Trix) in the database. We replace the attachment with `rich-text-attachable` tag with an `sgid`. When rendering that rich content again, we can render the attachables. This works for Remote URLs and for any Attachable record (more on that later).

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

Then this will convert this:

```php
$post->update([
    'content' => <<<HTML
    <div>
        <h1>Hello World</h1>
        <figure data-trix-attachment='{
            "url": "http://example.com/image.jpg",
            "width": 300,
            "height": 150,
            "contentType": "image/jpeg",
            "caption": "Something cool"
        }'>
            <img src="http://example.com/image.jpg" width="300" height="150" />
            <caption>
                Something cool
            </caption>
        </figure>
    </div>
    HTML,
])
```

to this:

```html
<div>
    <h1>Hello World</h1>
    <rich-text-attachable sgid="ALSklmasdklmKNAFKNAsdknknkn1@Kasd...=="></rich-text-attachable>
</div>
```

And when it renders it again, it will re-render the remote image again inside the `rich-text-attachable` tag.

### Attaching Models

You can have any model on your application as attachable inside a Trix rich text field. To do that, you need to implement the `AttachableContract` and use the `Attachable` trait in a model. Besides that, you also have to implement a `richTextRender(): string` where you can tell the package how to render that model inside Trix:

```php
use Tonysm\RichTextLaravel\Attachables\AttachableContract;
use Tonysm\RichTextLaravel\Attachables\Attachable;

class User extends Model implements AttachableContract
{
    use Attachable;

    public function richTextRender(): string
    {
        return view('users._mention', [
            'user' => $this,
        ])->render();
    }
}
```

Then inside that `users._mention` Blade template you have full control over the HTML for this attachable field.

### Getting Attachables

You can retrieve all the attachables of a rich content field using the `attachables()` method from the content instance:

```php
$post->content->attachables()
```

This will return a collection of all the attachables (anything that is attachable, really, so images and users, in this case).

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
