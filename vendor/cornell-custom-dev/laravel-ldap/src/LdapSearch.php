<?php

namespace CornellCustomDev\LaravelStarterKit\Ldap;

use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Static entry point for searching the Cornell LDAP directory, kept for backward compatibility.
 *
 * Each method delegates to the LdapService bound in the container. New code should use LdapService directly.
 */
class LdapSearch
{
    /**
     * Find a user by exact NetID.
     *
     * @throws LdapDataException
     */
    public static function getByNetid(string $netid, bool $bustCache = false): ?LdapData
    {
        return app(LdapService::class)->getById($netid, $bustCache);
    }

    /**
     * @throws LdapDataException
     */
    public static function getByEmail(string $email, bool $bustCache = false): ?LdapData
    {
        return app(LdapService::class)->getByEmail($email, $bustCache);
    }

    /**
     * Search LDAP with the given filter, cached by default.
     *
     * @throws InvalidArgumentException
     * @throws LdapDataException
     */
    public static function search(string $filter, bool $bustCache = false, ?array $attributes = null): ?Collection
    {
        return app(LdapService::class)->search($filter, $bustCache, $attributes);
    }
}
