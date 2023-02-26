<?php
/**
 * Created by PhpStorm.
 * User: baturkacamak
 * Date: 26/2/23
 * Time: 8:18
 */

namespace Tera;

interface CacheProviderInterface
{
    /**
     * Saves data to the cache using a given key.
     *
     * @param string $key The cache key to use.
     * @param mixed $data The data to be saved.
     * @return bool True on success, false on failure.
     */
    public function saveData(string $key, mixed $data): bool;

    /**
     * Retrieves data from the cache using a given key.
     *
     * @param string $key The cache key to use.
     * @return mixed The cached data, or null if not found.
     */
    public function fetchData(string $key): mixed;
}
