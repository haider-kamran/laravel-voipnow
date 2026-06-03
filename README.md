# Laravel VoipNow

[![Latest Stable Version](https://poser.pugx.org/hyderkamran/laravel-voipnow/v/stable)](https://packagist.org/packages/hyderkamran/laravel-voipnow)
[![Total Downloads](https://poser.pugx.org/hyderkamran/laravel-voipnow/downloads)](https://packagist.org/packages/hyderkamran/laravel-voipnow)
[![License](https://poser.pugx.org/hyderkamran/laravel-voipnow/license)](https://packagist.org/packages/hyderkamran/laravel-voipnow)

A production-ready Laravel package for interacting with:
- **VoipNow UnifiedAPI v5** (REST) — phone calls, events, presence, CDRs, faxes
- **VoipNow SystemAPI** (SOAP) — account provisioning, organizations, extensions, billing, PBX

Supports **Laravel 10, 11, 12** with **PHP 8.1+**.

---

## Features

- **Dual adapter** — REST (UnifiedAPI v5, default) and SOAP (SystemAPI, legacy)
- **Two facades** — `VoipNow` (REST) and `VoipNowSoap` (SystemAPI)
- **OAuth2 token management** — automatic acquisition and refresh per user or via cache
- **Typed HTTP methods** — `get()`, `post()`, `put()`, `patch()`, `delete()`
- **Pagination helper** — `paginate($resource, $page, $perPage)`
- **Resource finder** — `find($resource, $id)`
- **13 first-class REST helpers** — `GetServiceProviders()`, `GetOrganizations()`, `GetUsers()`, etc.
- **338+ SOAP operations** — full SystemAPI with IDE docblocks
- **Magic method fallback** — `VoipNow::GetCallQueues()` auto-maps to REST endpoint
- **Artisan command** — `php artisan voipnow:check` for connection diagnostics
- **Full test suite** — Orchestra Testbench + Mockery

---

## Requirements

| Requirement | Version |
|---|---|
| PHP | ^8.1, ^8.2, ^8.3 |
| Laravel | ^10.0, ^11.0, ^12.0 |
| GuzzleHttp | ^7.5 |
| php-soap ext | Required only for SOAP adapter |

---

## Installation

```bash
composer require hyderkamran/laravel-voipnow
```

Publish the config file and migration:

```bash
php artisan vendor:publish --provider="HyderKamran\VoipNow\VoipNowServiceProvider"
```

Run the migration (adds token columns to your `users` table):

```bash
php artisan migrate
```

---

## Configuration

Add these keys to your `.env` file:

```env
# Required
VOIPNOW_DOMAIN=https://voipnow.yourdomain.com
VOIPNOW_KEY=your-oauth2-client-id
VOIPNOW_SECRET=your-oauth2-client-secret

# Optional
VOIPNOW_ADAPTER=rest            # "rest" (default) or "soap"
VOIPNOW_VERSION=                # SOAP only — VoipNow version string e.g. 4.8.0
VOIPNOW_PARENT_IDENTIFIER=      # Scope calls to a specific parent account
VOIPNOW_WSDL_URL=               # SOAP only — override default WSDL URL
```

---

## Usage — UnifiedAPI v5 (REST)

The `VoipNow` facade targets the **VoipNow UnifiedAPI v5** for real-time telephony operations.

### Core HTTP Methods

```php
use VoipNow;

// GET /api/v5/organizations
VoipNow::get('organizations');
VoipNow::get('organizations', ['limit' => 10, 'offset' => 0]);

// POST /api/v5/organizations
VoipNow::post('organizations', ['name' => 'Acme Corp', 'email' => 'admin@acme.com']);

// PUT /api/v5/organizations/42
VoipNow::put('organizations/42', ['name' => 'Acme Corp Updated']);

// PATCH /api/v5/extensions/101
VoipNow::patch('extensions/101', ['display_name' => 'Reception']);

// DELETE /api/v5/organizations/42
VoipNow::delete('organizations/42');
```

### Pagination & Single-resource Helpers

```php
// Paginate: page 2, 25 records per page
VoipNow::paginate('users', page: 2, perPage: 25);

// Fetch single resource by ID
VoipNow::find('organizations', 42);
VoipNow::find('extensions', '101');
```

### First-class Resource Helpers

```php
VoipNow::GetServiceProviders();
VoipNow::GetOrganizations(['limit' => 50]);
VoipNow::GetOrganizationDetails(['ID' => 42]);
VoipNow::GetOrganizationDetails(['identifier' => 'acme']);
VoipNow::GetUsers();
VoipNow::GetExtensions(['organization_id' => 5]);
VoipNow::GetUserGroups();
VoipNow::GetChargingPlans();
VoipNow::GetPhoneNumbers();
VoipNow::GetCallQueues();
VoipNow::GetIVRs();
VoipNow::GetSounds();
VoipNow::GetCallHistory(['from' => '2024-01-01', 'to' => '2024-01-31']);
VoipNow::GetSystemInfo();
```

### Magic Method Fallback

Any `Get*`, `Add*`, `Update*`, `Remove*` call is automatically resolved to its REST endpoint:

```php
VoipNow::GetFaxes();               // GET  /api/v5/faxes
VoipNow::AddExtensions([...]);     // POST /api/v5/extensions
VoipNow::UpdateUsers([...]);       // PUT  /api/v5/users
VoipNow::RemoveExtensions([...]);  // DELETE /api/v5/extensions
```

---

## Usage — SystemAPI (SOAP)

The `VoipNowSoap` facade (or `VoipNow::soap()`) targets the **VoipNow SystemAPI** for account provisioning.

> **Note:** Requires the `php-soap` extension. Set `VOIPNOW_ADAPTER=soap` is **not** required — the SOAP client is always available independently.

### Via the Dedicated Facade

```php
use VoipNowSoap;

// Service Providers
VoipNowSoap::GetServiceProviders();
VoipNowSoap::AddServiceProvider(['name' => 'My SP', 'email' => 'sp@example.com']);
VoipNowSoap::GetServiceProviderDetails(['identifier' => 'my-sp']);

// Organizations
VoipNowSoap::GetOrganizations();
VoipNowSoap::AddOrganization(['name' => 'Acme', 'serviceProviderIdentifier' => 'my-sp']);
VoipNowSoap::GetOrganizationDetails(['identifier' => 'acme']);
VoipNowSoap::RemoveOrganization(['identifier' => 'acme']);

// Extensions
VoipNowSoap::GetExtensions(['organizationIdentifier' => 'acme']);
VoipNowSoap::AddExtension(['number' => '100', 'name' => 'Reception']);
VoipNowSoap::GetExtensionDetails(['identifier' => '100']);
VoipNowSoap::SetExtensionVoicemail(['identifier' => '100', 'active' => true]);

// Users
VoipNowSoap::GetUsers();
VoipNowSoap::AddUser(['login' => 'john', 'password' => 'secret']);
VoipNowSoap::GetUserDetails(['identifier' => 'john']);

// Billing
VoipNowSoap::GetChargingPlans();
VoipNowSoap::AddChargingPlan(['name' => 'Basic Plan']);
VoipNowSoap::GetCallingCards();

// PBX
VoipNowSoap::GetQueues(['organizationIdentifier' => 'acme']);
VoipNowSoap::GetIVRs(['organizationIdentifier' => 'acme']);
VoipNowSoap::GetConferences();

// Reports
VoipNowSoap::GetCallReport(['from' => '2024-01-01', 'to' => '2024-01-31']);
VoipNowSoap::GetSIPReport();

// Global
VoipNowSoap::GetTimezones();
VoipNowSoap::GetLanguages();
```

### Via the REST Facade's soap() Accessor

```php
use VoipNow;

VoipNow::soap()->GetOrganizations();
VoipNow::soap()->AddExtension(['number' => '200', 'name' => 'Sales']);
VoipNow::soap()->call('GetExtensionDetails', ['identifier' => '200']);
```

### Via the Container

```php
$soapClient = app('voipnow.soap');
$soapClient->GetServiceProviders();
```

---

## Artisan Commands

```bash
# Test the VoipNow API connection and display config status
php artisan voipnow:check
```

Example output:
```
VoipNow Connection Check
──────────────────────────────────────────────────
  Adapter:     rest
  Domain:      https://voipnow.yourdomain.com
  Key:         ✓ Set
  Secret:      ✓ Set

Testing connection...
✓ Connection successful!

Server Info:
  version: 4.8.0
  ...
```

---

## Testing

```bash
composer test
```

---

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email kamrankhosa40@gmail.com instead of using the issue tracker.

## Credits

- [Kamran Haider](https://github.com/haider-kamran)

## Support

[Please open an issue in GitHub](https://github.com/haider-kamran/laravel-voipnow/issues)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
