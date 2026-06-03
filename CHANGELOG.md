# Changelog

All notable changes to `hyderkamran/laravel-voipnow` will be documented in this file.

## [2.0.0] — 2026-06-03

### Breaking Changes
- Package renamed from `kamrankhosa/laravel-voipnow` to `hyderkamran/laravel-voipnow`
- PHP namespace changed from `KamranKhosa\VoipNow` to `HyderKamran\VoipNow`
- `VoipNowClass` renamed to `VoipNowClient`
- `SoapAdapter` replaced with `RestAdapter` as the default adapter
- `ConnectorInterface` moved from `Interface\` to `Contracts\` namespace
- `ConnectorInterface::connect()` return type changed from `object` to `static`

### Added
- **Dual adapter architecture** — REST (UnifiedAPI v5, default) and SOAP (SystemAPI, legacy)
- **`VoipNowClient`** — full REST client with `get()`, `post()`, `put()`, `patch()`, `delete()`
- **`VoipNowSoapClient`** — dedicated SystemAPI (SOAP) client with docblock for all 338+ operations
- **`VoipNow::soap()`** — facade accessor to reach the SOAP client from the REST facade
- **`Enums/AdapterType`** — type-safe enum for `rest` / `soap` adapter selection
- **`Enums/HttpMethod`** — type-safe enum for HTTP methods
- **`Traits/ResolvesMagicEndpoints`** — magic StudlyCase → kebab-case endpoint resolution
- **`Traits/HandlesVoipNowConfig`** — shared config normalisation, domain, token endpoint helpers
- **`Exception/VoipNowException`** — package-specific runtime exception
- First-class resource helpers: `GetServiceProviders`, `GetOrganizations`, `GetOrganizationDetails`, `GetUsers`, `GetExtensions`, `GetUserGroups`, `GetChargingPlans`, `GetSystemInfo`, `GetPhoneNumbers`, `GetCallQueues`, `GetIVRs`, `GetSounds`, `GetCallHistory`
- Magic method fallback maps `Get*` → GET, `Add*` → POST, `Update*` → PUT, `Remove*` → DELETE
- Token auto-refresh — stored on the Auth user model with cache fallback for CLI/queued jobs
- Support for Laravel 10, 11, and 12
- Support for PHP 8.1, 8.2, 8.3
- Full test suite with Orchestra Testbench and Mockery

### Changed
- `ServiceProvider` now registers both `voipnow` (REST) and `voipnow.soap` (SOAP) as separate singletons
- `AdapterType` enum drives adapter selection instead of string comparison
- Config key `voip_domain` replaces `domain` for clarity
- Migration stub file name fixed to include underscore separator

### Removed
- Legacy `SoapAdapter` as default — replaced by `RestAdapter` targeting UnifiedAPI v5
- Inline `use Auth;` anti-pattern — replaced with `Illuminate\Support\Facades\Auth`
- `larapack/dd` dev dependency

## [1.0.0] — Initial Release

- Basic SOAP adapter wrapping the VoipNow SystemAPI
- OAuth2 token acquisition stored on Auth user model
- Facade and service provider registration