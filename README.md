# Laravel VoipNow

[![Latest Stable Version](https://poser.pugx.org/hyderkamran/laravel-voipnow/v/stable)](https://packagist.org/packages/hyderkamran/laravel-voipnow)
[![Total Downloads](https://poser.pugx.org/hyderkamran/laravel-voipnow/downloads)](https://packagist.org/packages/hyderkamran/laravel-voipnow)
[![License](https://poser.pugx.org/hyderkamran/laravel-voipnow/license)](https://packagist.org/packages/hyderkamran/laravel-voipnow)

A laravel 10 package to interact with voipnow System API

**Note:** The token credential information will be stored to the users table, with token and expiry information for the authenticated user.

## Installation

You can install the package via composer:

```bash
composer require hyderkamran/laravel-voipnow
```

From the command-line run:

```bash
php artisan vendor:publish --provider="HyderKamran\VoipNow\VoipNowServiceProvider"
```

Add the following keys to your .env file.

```env
VOIPNOW_VERSION=
VOIPNOW_DOMAIN=
VOIPNOW_KEY=
VOIPNOW_SECRET=
```

The following key is optional

```env
VOIPNOW_PARENT_IDENTIFIER=
```

## Usage

You can call a VoipNow SystemAPI method directly by using the facace (e.g. `VoipNow::{VOIPNOWFUNCTION}`). For a full reference of all the available functions refer to the [VoipNow SystemAPI documenatation](https://wiki.4psa.com/display/VNUAPI30/VoipNow+SystemAPI).

### Examples

Retrieve a  list of all the service providers

``` php
use VoipNow;

return VoipNow::GetServiceProviders();
```

Retrieve the organization account details

```php
use VoipNow;

return VoipNow::GetOrganizationDetails(['identifier' => 'XXX']);
OR
return VoipNow::GetOrganizationDetails(['ID' => 'XXX']);
```

If you do not use the Facade, you can call it with the app() helper.

```php
$voipNow = app('voipnow');

return $voipNow->GetOrganizationDetails(['identifier' => 'XXX']);
OR
return $voipNow->GetOrganizationDetails(['ID' => 'XXX']);
```
## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email development@go-trex.com instead of using the issue tracker.

## Credits

- [Kamran Haider](https://github.com/haider-kamran)

## Support

[Please open an issue in github](https://github.com/haider-kamran/laravel-voipnow/issues)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
