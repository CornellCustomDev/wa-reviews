# CUAuth

Middleware for authorizing Laravel users.

- [CUAuth SSO](#cuauth) - Single sign-on and authorization middleware 
  - SAML PHP Toolkit integration
  - Apache mod_shib integration
- [AppTesters](#apptesters) - Limit access to users in the `APP_TESTERS` environment variable
- [Local Login](#local-login) - Allow Laravel users to log in with a local username and password
- [Livewire Auth](#livewire-auth) - Block unauthenticated Livewire update requests

## Use Cases

- **Single Sign-On**: Protect routes with SSO (mod_shib or PHP SAML)
  - Optionally log in SSO users to app user accounts
- **AppTesters**: Limit access on non-production sites to users in the `APP_TESTERS` environment variable


## Single Sign-On

### Usage

```php
// File: routes/web.php
use CornellCustomDev\LaravelStarterKit\CUAuth\Middleware\CUAuth;

Route::group(['middleware' => [CUAuth::class]], function () {
    // Protected routes here
    Route::get('profile', [UserController::class, 'show']);
});
```

```dotenv
# File: .env
# apache-shib (default) | php-saml
CU_AUTH_IDENTITY_MANAGER=apache-shib
```

See [Authorization](#identity-and-authorization) for details on how to log in remote users for local authorization.

> _See also: [shibboleth configuration](SHIBBOLETH.md)._

---

### Routing

Any pages protected by middleware are automatically redirected to SSO. To directly trigger log in or log out, use the following routes (parameters are optional and will default to `'/'`):
- Login: `route('cu-auth.sso-login', ['redirect_url' => '/home'])` 
- Logout: `route('cu-auth.sso-logout', ['return' => '/'])`

### Certs and Metadata (php-saml)

For using the PHP SAML Toolkit, the SAML keys and certs can be generated with the following command:

```bash
  php artisan cu-auth:generate-keys
```

It is possible to have composer automatically install the keys on `composer install` by adding the following to the `scripts` section of `composer.json`, which will only install the keys if they do not already exist:

```json
"scripts": {
    "post-install-cmd": [
        "@php artisan cu-auth:generate-keys"
    ]
}
```

The default location for the SAML keys and certs is in `storage/app/keys`. This location is configurable in the `config/cu-auth.php` file or by setting the `SAML_CERT_PATH` in `.env`.

### Stable SAML SP keys in container deployments

Containers start with an empty filesystem, so `cu-auth:generate-keys` creates a new SP key pair on every deploy. The SP 
certificate is part of the metadata registered with the IdP, so a new key pair breaks login until the registration is 
updated. For a stable SP, generate the keys once and store `sp_key.pem` and `sp_cert.pem` as secrets. Write them to the 
cert path (`storage/app/keys/` by default) before `cu-auth:generate-keys` runs, and the command keeps them and downloads 
only the IdP certificate (or, with IdP selection, the IdP settings files).

### Multiple IdPs (php-saml)

`SAML_IDP_SELECTION` sets how php-saml gets the IdP to log in with:

- `single` (the default) uses the one IdP in `config/php-saml-toolkit.php`.
- `discovery` lets users choose between Cornell NetID and Weill CWID at CIT's discovery service (idselect).
- `page` lets users choose on this package's selection page at `/sso/select`.

```dotenv
# File: .env
SAML_IDP_SELECTION=page # single | discovery | page
# SAML_IDP_DISCOVERY_URL=https://idselect-test.idm.cit.cornell.edu/idselect/select.html
# SAML_METADATA_URL_CORNELL=https://shibidp-test.cit.cornell.edu/idp/shibboleth
# SAML_METADATA_URL_WEILL=https://login-test.weill.cornell.edu/idp/saml2/idp/metadata.php
```

With `discovery` or `page`, run `php artisan cu-auth:generate-keys` to download each IdP's settings. Links to 
`/sso/login?entityID=<IdP entityID>` skip the choice and go straight to that IdP. The IdPs offered are 
`idp_selection.discovery.idps` or `idp_selection.page.idps` (with their labels and info links) in `config/cu-auth.php`.

#### The selection page in your site's layout

By default, `/sso/select` is a standalone page styled after idselect. To show the choice in your site's layout instead, 
create a view that wraps the `<x-cu-auth::idp-selection />` component in your layout:

```blade
{{-- File: resources/views/auth/select-login.blade.php --}}
<x-layouts.app title="Select Your Cornell Login">
    <x-cu-auth::idp-selection heading="" />
</x-layouts.app>
```

Then point the selection page at it:

```dotenv
# File: .env
SAML_IDP_SELECTION_VIEW=auth.select-login
```

The component renders a heading from `cu-auth.idp_selection.page.heading`, "Select Your Cornell Login" by default (set 
`SAML_IDP_SELECTION_HEADING` to change it everywhere, `heading="..."` to change it for one use, or `heading=""` to leave 
it out when your layout already shows it) and one button per IdP. The buttons return to the `redirect_url` query 
parameter after login, or `/`; pass `redirect-url="..."` to use another URL. The component can be used on any page, 
and renders nothing outside `page` mode.

To change the package's views themselves, publish them to `resources/views/vendor/cu-auth`:

```bash
php artisan vendor:publish --tag=starterkit:cu-auth-views
```


For `apache-shib`, IdP selection is configured in mod_shib instead; see the `idselect` ApplicationOverride in
[shibboleth configuration](SHIBBOLETH.md#shibboleth-with-multiple-idps).

---

### Local Testing (apache-shib)
For local testing where mod_shib is not available, the `REMOTE_USER` environment variable can be set to simulate
Shibboleth authentication. Note that `APP_ENV` must be set to "local" for this to work and the config cache must be cleared when `REMOTE_USER` is changed.

```dotenv
# File: .env
APP_ENV=local
REMOTE_USER=abc123
```

## Identity and Authorization

Once authenticated via SSO, the user's identity is available via the `IdentityManager`, which can be accessed via the app container.

```php
use CornellCustomDev\LaravelStarterKit\CUAuth\Managers\IdentityManager;

$remoteIdentity = app(IdentityManager::class)->getIdentity();
$netid = $remoteIdentity->id(); // NetID | CWID

// If provided with SSO attributes:
$email = $remoteIdentity->email(); // Primary email (i.e. netid@cornell.edu)
$name = $remoteIdentity->name(); // Display name
```

The SAML attributes available are based on the CIT-documented list: https://it.cornell.edu/shibboleth/shibboleth-faq.

### User authorization

If the site should manage authorization for users in the application, set `config('cu-auth.require_local_user')` to true:

```php
# File: config/cu-auth.php
'require_local_user' => env('REQUIRE_LOCAL_USER', true),
```

Requiring a local user triggers the `CUAuthenticated` event when a user is authenticated via single sign-on. The site must
[register a listener](https://laravel.com/docs/11.x/events#registering-events-and-listeners) for
the `CUAuthenticated` event. This listener should look up the user in the database and log them in or create a user
as needed.

> [AuthorizeUser](Listeners/AuthorizeUser.php) is provided as a starting point for handling the CUAuthenticated event.
> It is simplistic and should be replaced with a site-specific implementation in the site code base. It demonstrates 
> retrieving user data via the [IdentityManager](Managers/IdentityManager.php) and creating a user if they do not exist. 


## Configuration

See [config/cu-auth.php](../../config/cu-auth.php) for configuration options, all of which can be set with environment variables.

To modify the default configuration, publish the configuration file:

```bash
  php artisan vendor:publish --tag=starterkit:cu-auth-config
```

To modify the PHP SAML Toolkit configuration, publish the configuration file:

```bash
  php artisan vendor:publish --tag=starterkit:php-saml-toolkit-config
```


## AppTesters

Limits non-production access to users in the `APP_TESTERS` environment variable.

### Usage

```php
// File: routes/web.php
use CornellCustomDev\LaravelStarterKit\CUAuth\Middleware\AppTesters;
use CornellCustomDev\LaravelStarterKit\CUAuth\Middleware\CUAuth;

Route::group(['middleware' => [CUAuth::class, AppTesters::class], function () {
    Route::get('profile', [UserController::class, 'show']);
});
```

```dotenv
# File: .env
APP_TESTERS=abc123,def456
```

On non-production sites, the [AppTesters](Middleware/AppTesters.php) middleware checks the "APP_TESTERS" environment variable for a comma-separated list of users. If a user is logged in and not in the list, the middleware will return an HTTP_FORBIDDEN response.

The field used for looking up users is `netid` by default. It is configured in [config/cu-auth.php](../../config/cu-auth.php) file as `app_testers_field`.


## Local Login
For testing purposes, the environment variable "ALLOW_LOCAL_LOGIN" can be set to true to bypass the middleware for a currently authenticated user.
```dotenv
# File: .env
ALLOW_LOCAL_LOGIN=true
```


## Livewire Auth

Blocks unauthenticated POST requests to `/livewire/update`, preventing anonymous users from interacting with Livewire components.

### Usage

```dotenv
# File: .env
REQUIRE_LIVEWIRE_AUTH=true
```
