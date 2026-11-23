# CD-LaravelLdap

A Laravel package for retrieving Cornell University LDAP data, with support for Weill Cornell Medicine.

This package provides two main classes:
- [`LdapService`](src/LdapService.php): Handles LDAP queries and caching for one directory
- [`LdapData`](src/LdapData.php): An immutable data object that encapsulates Cornell LDAP attributes in a well-defined structure

[`LdapSearch`](src/LdapSearch.php) is a static wrapper around the Cornell `LdapService`.

## Installation
```bash
composer require cornell-custom-dev/laravel-ldap
php artisan vendor:publish --tag=ldap-config
```

```dotenv
# File: .env
LDAP_USER=username
LDAP_PASS=password
```

See [`config/ldap.php`](config/ldap.php) for additional settings.

The package registers an `LdapService` singleton for the Cornell directory, built from `config('ldap')`, available
as `app('ldap.cornell')` or `app(LdapService::class)`.

## Usage

### Single lookup
```php
try {
    $ldapData = app('ldap.cornell')->getById($netid);
    $displayName = $ldapData->name();
} catch (LdapDataException $e) {
    // Handle exceptions
}
```

### Collection of search results
```php
use CornellCustomDev\LaravelStarterKit\Ldap\LdapData;

try {
    $searchFilter = "(|(uid=$this->search*)(displayname=*$this->search*)(mail=$this->search*))";
    return app('ldap.cornell')->search($searchFilter)
        ?->mapWithKeys(fn (LdapData $ldapData) => [
            'name'  => $ldapData->name(),
            'email' => $ldapData->primaryEmail(),
        ]);
} catch (LdapDataException $e) {
    // Handle exceptions
}
```

`LdapService` caches queries for a configurable duration (default 300 seconds), so multiple calls for the same
ldap filter and attributes are not expensive.

Documentation of all currently parsed fields can be found in [LdapData.php](src/LdapData.php).

### LdapSearch

`LdapSearch::getByNetid()`, `getByEmail()` and `search()` are static wrappers around the Cornell `LdapService`.

```php
use CornellCustomDev\LaravelStarterKit\Ldap\LdapSearch;

$ldapData = LdapSearch::getByNetid($netid);
```

### Other directories

To search another directory, construct an `LdapService` with that directory's settings. Settings for the Weill
Cornell Medicine directory are included in [`config/ldap.php`](config/ldap.php) under `weill`.

Register the service in your `AppServiceProvider`:

```php
$this->app->singleton('ldap.weill', fn () => new LdapService(
    server: config('ldap.weill.server'),
    user: config('ldap.weill.user'),
    pass: config('ldap.weill.pass'),
    baseDn: config('ldap.weill.base_dn'),
    domain: config('ldap.weill.domain'),
    idAttribute: config('ldap.weill.id_attribute'),
    attributes: config('ldap.weill.attributes'),
));
```

```php
try {
    $ldapData = app('ldap.weill')->getById($cwid); // Searches weillCornellEduCWID
    $emplid = $ldapData?->emplid;
} catch (LdapDataException $e) {
    // Handle exceptions
}
```

---

## Additional LDAP Attributes

`LdapSearch::getByNetid($netid)->returnedData` is an array of all LDAP attributes returned, keyed by attribute 
name. The set of attributes is a subset of the attributes documented at
https://confluence.cornell.edu/pages/viewpage.action?spaceKey=IDM&title=Attributes.

## Legacy Compatibility

[LdapData](src/LdapData.php) has a property to provide compatibility with the `LDAP::data()` method from the
legacy `App\Helpers\LDAP` class that exists on many Cornell Laravel sites.

```php
$ldapData = LdapSearch::getByNetid($netid)?->ldapData;
```

The value of this property matches the output of `LDAP::data($netid)`, but with the 'count' and 'count_values'
keys removed.
