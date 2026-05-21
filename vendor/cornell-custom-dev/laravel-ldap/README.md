# CD-LaravelLdap

A Laravel package for retrieving Cornell University LDAP data.

This package provides two main classes:
- [`LdapSearch`](src/LdapSearch.php): Handles LDAP queries and caching
- [`LdapData`](src/LdapData.php): An immutable data object that encapsulates Cornell LDAP attributes in a well-defined structure

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

## Usage

### Single lookup
```php
use CornellCustomDev\LaravelStarterKit\Ldap\LdapSearch;

try {
  $ldapData = LdapSearch::getByNetid($netid);
  $displayName = $ldapData->name();
} catch (LdapDataException $e) {
  // Handle exceptions
}
```

### Collection of search results
```php
use CornellCustomDev\LaravelStarterKit\Ldap\LdapSearch;
use CornellCustomDev\LaravelStarterKit\Ldap\LdapData;

try {
    $searchFilter = "(|(uid=$this->search*)(displayname=*$this->search*)(mail=$this->search*))";
    return LdapSearch::search($searchFilter)
        ?->mapWithKeys(fn (LdapData $ldapData) => [
            'name'  => $ldapData->name(),
            'email' => $ldapData->email(),
        ]);
} catch (LdapDataException $e) {
  // Handle exceptions
}
```

LdapSearch caches queries for a configurable duration (default 300 seconds), so multiple calls for the same 
ldap filter are not expensive.

Documentation of all currently parsed fields can be found in [LdapData.php](src/LdapData.php).

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
