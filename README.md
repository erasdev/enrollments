# A package for managing enrollments and enrollment rules

Make any model enrollable.

## Installation

You can install the package via composer:

```bash
composer require erasdev/enrollments
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="enrollments-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="enrollments-config"
```

<!-- This is the contents of the published config file:

```php
return [
];
``` -->

<!-- Optionally, you can publish the views using -->

<!-- ```bash
php artisan vendor:publish --tag="enrollments-views"
``` -->

## Usage

```php
$enrollments = new Erasdev\Enrollments();
echo $enrollments->echoPhrase('Hello, Erasdev!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Era](https://github.com/erasdev)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
