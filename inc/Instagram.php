<?php
/**
 * Created by PhpStorm.
 * User: baturkacamak
 * Date: 26/2/23
 * Time: 10:02
 */

namespace Tera;

<?
php

namespace Tera;

use WP_Error;

class Instagram
{
    public function __construct(
        private InstagramDataProviderInterface $instagramDataProvider,
        private PostHtmlBuilderInterface $postHtmlBuilder,
        private CacheProviderInterface $cacheProvider
    ) {
        $this->register();
    }

    public function register()
    {
        add_action('wp_ajax_get_instagram_feed', [$this, 'scrapeInstagram']);
    }

    public function scrapeInstagram()
    {
        if (!defined('DOING_AJAX') && !DOING_AJAX || !isset($_POST['username'])) {
            return false;
        }

        $username    = sanitize_text_field($_POST['username']);
        $transientId = 'instagram-' . sanitize_title_with_dashes($username);
        $data        = $this->cacheProvider->fetchData($transientId);

        if (!$data) {
            $data = $this->instagramDataProvider->fetchData("https://instagram.com/{$username}");
            $this->cacheProvider->saveData($transientId, $data);
        }

        if (!$data) {
            return new WP_Error(
                'no_data',
                esc_html__('Failed to fetch Instagram data.', 'terawp')
            );
        }

        $posts = $this->parseData($data);
        $html  = $this->postHtmlBuilder->buildHtml($posts);

        echo $html;
        exit();
    }

    private function parseData(string $data): array
    {
        $parser = new InstagramDataParser($data);

        return $parser->parseData();
    }
}
