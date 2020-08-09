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
if (! defined('ABSPATH')) {
    exit;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

/**
 * Block Initializer.
 */
require_once plugin_dir_path(__FILE__) . 'src/init.php';
