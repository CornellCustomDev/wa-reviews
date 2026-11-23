<?php

use CornellCustomDev\LaravelStarterKit\Ldap\LdapService;

return [
    /*
    |----------------------------------------------------------------------
    | LDAP Credentials
    |----------------------------------------------------------------------
    |
    | The credentials used to connect to the LDAP server, which must be set
    | in the .env file.
    |
    */
    'user' => env('LDAP_USER') ?? '',
    'pass' => env('LDAP_PASS') ?? '',

    /*
    |----------------------------------------------------------------------
    | LDAP Server
    |----------------------------------------------------------------------
    |
    | The LDAP server to connect to and base_dn, defaulting to the Cornell
    | LDAP server and base DN.
    |
    */
    'server' => env('LDAP_SERVER', 'ldaps://query.directory.cornell.edu'),
    'base_dn' => env('LDAP_BASE_DN', 'ou=People,o=Cornell University,c=US'),

    /*
    |----------------------------------------------------------------------
    | LDAP Cache
    |----------------------------------------------------------------------
    |
    | The number of seconds to cache LDAP queries. This is useful for
    | performance, but be careful not to cache too long, as LDAP data
    | can change frequently.
    |
    */
    'cache_seconds' => env('LDAP_CACHE_SECONDS', 300),

    /*
    |----------------------------------------------------------------------
    | LDAP Debug
    |----------------------------------------------------------------------
    |
    | Log LDAP results to the default log channel on non-production sites.
    |
    */
    'debug' => env('LDAP_DEBUG', false),

    /*
    |----------------------------------------------------------------------
    | Weill Cornell Medicine LDAP
    |----------------------------------------------------------------------
    |
    | Settings for an LdapService that searches the Weill directory. The
    | package does not bind this service; an application constructs it
    | from these settings, for example as a singleton in its own service
    | provider. See the README.
    |
    | WEILL_LDAP_USER is a fully-qualified bind DN.
    |
    */
    'weill' => [
        'user' => env('WEILL_LDAP_USER', ''),
        'pass' => env('WEILL_LDAP_PASS', ''),
        'server' => env('WEILL_LDAP_SERVER', ''),
        'base_dn' => env('WEILL_LDAP_BASE_DN', 'ou=people,dc=weill,dc=cornell,dc=edu'),
        'domain' => 'med.cornell.edu',
        'id_attribute' => 'weillCornellEduCWID',
        'attributes' => LdapService::WEILL_ATTRIBUTES,
    ],
];
