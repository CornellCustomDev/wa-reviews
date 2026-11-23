<?php

namespace CornellCustomDev\LaravelStarterKit\Ldap;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Searches one LDAP directory.
 *
 * Defaults to the Cornell directory from config('ldap').
 */
class LdapService
{
    /**
     * Default attributes to retrieve from the Cornell LDAP directory.
     *
     *  See https://confluence.cornell.edu/display/IDM/Attributes
     */
    public const DEFAULT_ATTRIBUTES = [
        'uid',
        'edupersonprincipalname',
        'displayName',
        'givenName',
        'sn',
        'mail',
        'cornelleduprimaryaffiliation',
        'cornelleduaffiliation',
        'cornelleduemplid',
        'cornelledudeptname1',
        'cornelleduwrkngtitle1',
        'cornelleducampusphone',
        'cornelleduprefgivenname',
        'cornelleduprefsn',
        'cornelledupreviousnetids',
        'cornelledupreviousemplids',
        'cornelleduacadcollege',
    ];

    /**
     * Default attributes to retrieve from the Weill Cornell Medicine LDAP directory.
     */
    public const WEILL_ATTRIBUTES = [
        'uid',
        'weillCornellEduCWID',
        'displayName',
        'givenName',
        'sn',
        'mail',
        'cornelleduemplid',
    ];

    /**
     * @param  string  $server  An ldaps:// URI, with a port if it is not 636
     * @param  string  $user  The fully-qualified DN to bind with
     * @param  string  $pass  The bind password
     * @param  string  $baseDn  The base DN to search under
     * @param  string  $domain  Used for the principalName when the directory does not return one
     * @param  string  $idAttribute  The attribute that getById() searches: uid (NetID) for Cornell, weillCornellEduCWID for Weill
     * @param  array<string>  $attributes  The attributes retrieved by default
     */
    public function __construct(
        public readonly string $server,
        public readonly string $user,
        public readonly string $pass,
        public readonly string $baseDn,
        public readonly string $domain = 'cornell.edu',
        public readonly string $idAttribute = 'uid',
        public readonly array $attributes = self::DEFAULT_ATTRIBUTES,
    ) {}

    /**
     * Find the entry whose idAttribute exactly matches the given id (a NetID for Cornell, a CWID for Weill).
     *
     * @throws LdapDataException
     */
    public function getById(string $id, bool $bustCache = false): ?LdapData
    {
        if (empty(trim($id))) {
            throw new InvalidArgumentException('LdapService::getById requires a search term');
        }
        $id = self::escape($id);

        return $this->search("($this->idAttribute=$id)", $bustCache)?->first();
    }

    /**
     * @throws LdapDataException
     */
    public function getByEmail(string $email, bool $bustCache = false): ?LdapData
    {
        if (empty(trim($email))) {
            throw new InvalidArgumentException('LdapService::getByEmail requires a search term');
        }
        $email = self::escape($email);

        return $this->search("(|(mail=$email)(edupersonprincipalname=$email))", $bustCache)?->first();
    }

    /**
     * Search LDAP with the given filter, cached by default.
     *
     * @return Collection<string, LdapData>|null
     *
     * @throws InvalidArgumentException
     * @throws LdapDataException
     */
    public function search(string $filter, bool $bustCache = false, ?array $attributes = null): ?Collection
    {
        // Trap for empty strings
        if (empty(trim($filter))) {
            throw new InvalidArgumentException('LdapService::search requires a search term');
        }
        if (empty($this->user) || empty($this->pass)) {
            throw new LdapDataException('LDAP bind credentials are not configured.');
        }
        $attributes ??= $this->attributes;

        // Key on everything that shapes the result
        $cacheKey = 'LdapService::search_'.md5(json_encode([
            $this->server, $this->baseDn, $filter, $attributes,
        ]));
        if ($bustCache) {
            Cache::forget($cacheKey);
        }

        return Cache::remember(
            key: $cacheKey,
            ttl: now()->addSeconds(config('ldap.cache_seconds')),
            callback: fn () => $this->performSearch($filter, $attributes),
        );
    }

    /**
     * Escape a value for use in an LDAP filter.
     */
    public static function escape(string $value): string
    {
        return ldap_escape($value, '', LDAP_ESCAPE_FILTER);
    }

    /**
     * Perform an LDAP search with the given filter, returning a collection of LdapData objects.
     *
     * @param  array<string>  $attributes
     *
     * @throws LdapDataException
     */
    private function performSearch(string $filter, array $attributes): ?Collection
    {
        try {
            $connection = ldap_connect($this->server);
            if (! $connection) {
                throw new LdapDataException('Could not connect to LDAP server.');
            }

            // Set options for performance
            ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($connection, LDAP_OPT_SIZELIMIT, 1000);
            ldap_set_option($connection, LDAP_OPT_TIMELIMIT, 3);

            // ldap_connect() doesn't open a socket, so without these an unreachable server
            // hangs the bind until the OS gives up on the TCP connection
            ldap_set_option($connection, LDAP_OPT_NETWORK_TIMEOUT, 5);
            ldap_set_option($connection, LDAP_OPT_TIMEOUT, 5);

            // Bind to the LDAP server
            $result = ldap_bind_ext($connection, $this->user, $this->pass);
            if ($result === false) {
                $error_message = ldap_error($connection);
                throw new LdapDataException("Could not bind to LDAP server: $error_message");
            }

            // Confirm that the bind was successful. ldap_parse_result() returns true whenever the
            // response parses, so a rejected bind (e.g., 49 Invalid credentials) shows up only in $error_code.
            if (! ldap_parse_result($connection, $result, $error_code, $matched_dn, $error_message)) {
                $error_message = ldap_error($connection);
                throw new LdapDataException("Could not parse the response from ldap_bind: $error_message");
            }
            if ($error_code) {
                // The server's diagnostic (e.g., AD's "data 52e" vs. "data 2030") tells a bad password
                // from a bad bind DN, but is too noisy for the exception
                if (config('ldap.debug')) {
                    Log::debug('LDAP bind rejected', ['server' => $this->server, 'diagnostic' => $error_message]);
                }
                throw new LdapDataException('Error response from ldap_bind: '.ldap_err2str($error_code));
            }

            // Perform the LDAP search
            $result = ldap_search($connection, $this->baseDn, $filter, $attributes);
            if ($result === false) {
                return null;
            }

            $entries = ldap_get_entries($connection, $result);
            if ($entries === false || $entries['count'] === 0) {
                return null;
            }

            if (config('ldap.debug')) {
                Log::debug('LDAP search results', ['server' => $this->server, 'filter' => $filter, 'entries' => json_encode($entries, JSON_PRETTY_PRINT)]);
            }

            return $this->parseSearchResults($entries);

        } catch (Exception $e) {
            throw new LdapDataException($e->getMessage());
        } finally {
            if (isset($connection) && $connection) {
                ldap_close($connection);
            }
        }
    }

    /**
     * Parse LDAP search results into a collection of LdapData objects.
     *
     * @param  array  $entries  Raw LDAP search results
     * @return Collection Collection of LdapData objects indexed by uid
     */
    protected function parseSearchResults(array $entries): Collection
    {
        $count = $entries['count'];
        $results = collect();

        for ($i = 0; $i < $count; $i++) {
            $parsedEntry = self::parseEntry($entries[$i]);

            if (isset($parsedEntry['uid'])) {
                $ldapData = LdapData::make($parsedEntry, $this->domain);
                if ($ldapData) {
                    $results->put($parsedEntry['uid'], $ldapData);
                }
            }
        }

        return $results;
    }

    /**
     * Parse an entry from ldap_search into a simple array.
     */
    public static function parseEntry(?array $entry = []): array
    {
        unset($entry['dn']);
        $data = [];
        foreach ($entry as $key => $value) {
            if (is_numeric($key) || $key == 'count') {
                continue;
            }
            if ($value['count'] == 1) {
                $parsedValue = $value[0];
            } else {
                unset($value['count']);
                $parsedValue = $value;
            }
            // Only populate the field if we have data.
            if (! empty($parsedValue)) {
                $data[$key] = $parsedValue;
            }
        }

        return $data;
    }
}
