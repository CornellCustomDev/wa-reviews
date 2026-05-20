# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Run all tests
composer test

# Run a single test file
vendor/bin/phpunit tests/Feature/AppTestersTest.php

# Run a single test by name
vendor/bin/phpunit --filter testMethodName

# Lint / fix code style
vendor/bin/pint

# Full CI (prepare + test)
composer ci
```

## Architecture

This is a **Laravel package** (not an application) providing SSO authentication middleware for Cornell University apps. The namespace is `CornellCustomDev\LaravelStarterKit\CUAuth\`.

### Core abstraction: `IdentityManager`

`src/Managers/IdentityManager.php` is the central interface. `CUAuthServiceProvider` binds one concrete implementation as a singleton based on `CU_AUTH_IDENTITY_MANAGER`:

- **`ShibIdentityManager`** (`apache-shib`) — reads Shibboleth attributes from Apache server variables (`$_SERVER`). For local development, falls back to `REMOTE_USER` env var when `APP_ENV != production`.
- **`SamlIdentityManager`** (`php-saml`) — uses the OneLogin PHP-SAML toolkit for SAML SP flows.

Both return a `RemoteIdentity` data object (readonly class) that normalizes identity data from either IdP.

### Authentication flow

1. **`CUAuth` middleware** guards routes. It checks `IdentityManager::hasRemoteIdentity()` and redirects to `cu-auth.sso-login` if not authenticated. If `allow_local_login = true`, a locally-authenticated Laravel user bypasses the SSO check entirely (`isLoggedInLocally()` in the `ChecksLocalLogin` trait, shared with `LivewireAuth`).
2. If `require_local_user = true`, the middleware fires the `CUAuthenticated` event after SSO auth. The consuming app must listen for this event to log in or create a local Laravel user.
3. **`AppTesters` middleware** can be stacked after `CUAuth` to restrict non-production access to users listed in `APP_TESTERS`.

### Routes registered by the package

- `GET /sso/login` → `AuthController::login` (redirects to IdP)
- `GET /sso/logout` → `AuthController::logout` (SLO)
- `GET/POST /sso/acs` → `AuthController::acs` (SAML assertion consumer / Shib return; CSRF-exempt)
- `GET /sso/metadata` → `AuthController::metadata` (SAML SP metadata)

### `RemoteIdentity` key methods

- `id()` — NetID or CWID (unique within the IdP)
- `principalName()` — `eduPersonPrincipalName` (e.g., `netid@cornell.edu`); unique across Cornell and Weill IdPs
- `email()` — returns `principalName` if set, else alias mail
- `uniqueUid()` — **deprecated**; use `principalName()` for cross-IdP uniqueness

### Livewire support

Setting `REQUIRE_LIVEWIRE_AUTH=true` makes the service provider override Livewire's update route to require `LivewireAuth` middleware, blocking unauthenticated POSTs to `/livewire/update`.

### Testing

Tests use Orchestra Testbench. `FeatureTestCase` sets up an in-memory SQLite database and loads Laravel's default migrations. Unit tests extend `UnitTestCase`. The package has no database migrations of its own.
