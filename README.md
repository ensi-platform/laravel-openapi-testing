# Laravel OpenApi Testing

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ensi/laravel-openapi-testing.svg?style=flat-square)](https://packagist.org/packages/ensi/laravel-openapi-testing)
[![Tests](https://github.com/ensi-platform/laravel-openapi-testing/actions/workflows/run-tests.yml/badge.svg?branch=master)](https://github.com/ensi-platform/laravel-openapi-testing/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/ensi/laravel-openapi-testing.svg?style=flat-square)](https://packagist.org/packages/ensi/laravel-openapi-testing)

This packages is based on `ensi/openapi-httpfoundation-testing` and provides `ValidatesAgainstOpenApiSpec` trait

## Installation

You can install the package via composer:

```bash
composer require ensi/laravel-openapi-testing --dev
```

## Version Compatibility

| Laravel OpenApi Testing | PHP  |
|-------------------------|------|
| ^0.1.0                  | ^8.0 |
| ^0.2.0                  | ^8.0 |
| ^0.3.0                  | ^8.0 |
| ^0.4.0                  | ^8.1 |

## Basic usage

Let's add validation according to oas3 to our tests.
All we need is to `use ValidatesAgainstOpenApiSpec;` and implement `getOpenApiDocumentPath(): string` method like that:


```php
class SomeTestCase extends AnotherTestCase
{
    use ValidatesAgainstOpenApiSpec;

    protected function getOpenApiDocumentPath(): string
    {
        return public_path('api-docs/v1/index.yaml');
    }
}
```

The trait overrides `$this->call` method to add the needed validation
As a result all http related helper methods (`$this->get()`, `$this->postJson()` and e.t.c) perform the validation too.
Both request and response is validated to match some part of the given spec. If validation fails your tests is automatically marked as failed, no need to need any manual assertions.

### Turning validation off

In some cases you may want to turn validation for a specific request.
Here is an example how to do it:

```php
// Turn off validation for both request
$this->skipNextOpenApiRequestValidation()->getJson(...);

// Turn off validation for both response
$this->skipNextOpenApiResponseValidation()->getJson(...);

// Turn off validation for both request and response
$this->skipNextOpenApiValidation()->getJson(...);
```

### Mapping paths

In order to validate request against oas3 the package need to map it to one of the paths described in specification document.
We use path from Laravel's route (`$request->route()->uri`) for that purpose.
If it does not fully match in your case you can explicitly set OpenApi path for the current request like that:

```php
$this->forceOpenApiPath('/pets/{petId}')->getJson(...);
```

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

### Testing

1. composer install
2. composer test

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
