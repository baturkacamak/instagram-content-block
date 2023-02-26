<?php

/**
 * Plugin Name: Instagram Feed Block (Tera)
 * Plugin URI: https://github.com/ahmadawais/create-guten-block/
 * Description: Tera Instagram Block will provide you to show a public feed on your web site
 * Author: baturkacamak
 * Author URI: https://batur.info
 * Version: 1.0.0
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package CGB
 */

// Exit if accessed directly.
use Tera\CacheProvider;
use Tera\InstagramDataParser;
use Tera\InstagramDataProvider;
use Tera\PostHtmlBuilder;

if (!defined('ABSPATH')) {
    exit;
}

// Include the autoloader if it exists.
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

$cacheProvider = new CacheProvider('instagram_feed_cache', 120);
$dataParser    = new InstagramDataParser($cacheProvider->fetchData('teknoseyir'));
$dataProvider  = new InstagramDataProvider($dataParser);
$htmlBuilder   = new PostHtmlBuilder([320, 640, 960]);

new \Tera\Instagram($data_provider, $html_builder, $cache_provider);

// Include the plugin's initialization file.
require_once plugin_dir_path(__FILE__) . 'src/init.php';
