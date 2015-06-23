<?php
/**
 * Plugin Name: data-feed
 * Plugin URI: https://github.com/akvo/akvo-json-data-plugin
 * Description: Data feed provides an api for fetching items from data feeds in json or xml format.
 * Version: 1.1
 * Author: Andreas Jonsson
 * Author URI: http://kreablo.se
 * License: AGPL3
 * Text Domain: data-feed
 */

require_once __DIR__ . '/autoload.php';

register_activation_hook( __FILE__, 'DataFeed\Plugin\PluginManagement::activation' );
register_deactivation_hook( __FILE__, 'DataFeed\Plugin\PluginManagement::deactivation' );
register_uninstall_hook( __FILE__, 'DataFeed\Plugin\PluginManagement::uninstall' );

define( 'DATA_FEED_PLUGIN_DIR', __DIR__ );
define( 'DATA_FEED_PLUGIN_VERSION', '1.1' );

foreach ( array( 'admin_menu', 'plugins_loaded', 'admin_enqueue_scripts' ) as $a ) {
	add_action( $a, array( 'DataFeed\Plugin\PluginManagement', $a ) );
}

add_shortcode( 'data_feed', 'DataFeed\DataFeed::shortcode' );
add_filter( 'no_texturize_shortcodes', function ( $s ) { array_push( $s, 'data_feed' ); return $s; } );