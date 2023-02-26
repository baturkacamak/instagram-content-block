<?php
/**
 * Created by PhpStorm.
 * User: baturkacamak
 * Date: 26/2/23
 * Time: 8:18
 */

namespace Tera;

class CacheProvider implements CacheProviderInterface
{
    public function __construct(
        private string $transientPrefix,
        private int $cacheMinutes
    ) {
    }

    public function saveData(string $username, array $data): void
    {
        $transientId = sprintf('%s-%s', $this->transientPrefix, sanitize_title_with_dashes($username));

        // Do not set an empty transient - should help catch private or empty accounts.
        if (!empty($data)) {
            $cacheData = json_encode($data);
            set_transient($transientId, $cacheData, MINUTE_IN_SECONDS * $this->cacheMinutes);
        }
    }

    public function fetchData(string $key): mixed
    {
        $transient_name = $this->transientPrefix . '_' . $key;

        return get_transient($transient_name);
    }

}
