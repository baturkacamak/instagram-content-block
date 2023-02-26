<?php
/**
 * Created by PhpStorm.
 * User: baturkacamak
 * Date: 26/2/23
 * Time: 8:22
 */

namespace Tera;

use WP_Error;

class InstagramDataProvider implements InstagramDataProviderInterface
{
    public function __construct(
        private InstagramDataParserInterface $instagramDataParser,
        private string $instagramUrl = 'https://instagram.com'
    ) {
    }

    public function getInstagramData(string $username): array
    {
        $url = sprintf('%s/%s', $this->instagramUrl, $username);
        $response = wp_remote_get($url);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            throw new WP_Error('site_down', 'Unable to communicate with Instagram.');
        }

        $json = $this->instagramDataParser->parse($response['body']);

        if (!$json) {
            throw new WP_Error('bad_json', 'Instagram has returned invalid data.');
        }

        return $json;
    }
}
