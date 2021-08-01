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

TODO

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
