<?php

namespace DataFeed\Plugin;

use DataFeed\DataFeed;

class PluginManagement
{

	public static function activation()
	{
		DataFeed::component( DataFeed::FEED_STORE )->activate();
	}

	public static function deactivation()
	{
		DataFeed::component( DataFeed::FEED_STORE )->deactivate();
	}

	public static function uninstall()
	{
		DataFeed::component( DataFeed::FEED_STORE )->uninstall();
	}

	public static function admin_menu()
	{
		\add_options_page( __('Data feeds', 'data-feed'), __('Data feeds\' options', 'data-feed'), 'manage_options', 'data-feed-options', array('DataFeed\Admin\OptionsPage', 'page') );
	}

	public static function admin_enqueue_scripts( $page )
	{
		if ( $page == 'settings_page_data-feed-options' ) {
			\wp_enqueue_style( 'datafeed-optionpage-css', plugins_url( '/resources/css/optionspage.css' , DATA_FEED_PLUGIN_DIR . '/data-feed.php' ), array(), DATA_FEED_PLUGIN_VERSION );
			\wp_enqueue_script( 'datafeed-model-js', plugins_url( '/resources/js/datafeed.js', DATA_FEED_PLUGIN_DIR . '/data-feed.php'), array( 'underscore', 'backbone' ), DATA_FEED_PLUGIN_VERSION );
			\wp_enqueue_style( 'wp-jquery-ui-dialog' );
			\wp_enqueue_script( 'datafeed-optionpage-js', plugins_url( '/resources/js/optionspage.js', DATA_FEED_PLUGIN_DIR . '/data-feed.php' ), array( 'underscore', 'backbone', 'jquery-ui-dialog' ), DATA_FEED_PLUGIN_VERSION );
			\wp_enqueue_script( 'datafeed-editor-js', plugins_url( '/resources/js/editor.js', DATA_FEED_PLUGIN_DIR . '/data-feed.php' ), array( 'jquery-ui-dialog' ), DATA_FEED_PLUGIN_VERSION );
		}
	}

	public static function datafeed_service()
	{
		DataFeed::component( DataFeed::REST_SERVICE )->handle();
	}

	public static function plugins_loaded()
	{
		\load_plugin_textdomain( 'data-feed', false, DATA_FEED_PLUGIN_DIR . '/i18n' );
		\add_action( 'wp_ajax_datafeed_service', 'DataFeed\Plugin\PluginManagement::datafeed_service' );
	}

	public static function widgets_init() {
		\register_widget( 'DataFeed\Widget\DataFeedWidget' );
		\register_widget( 'DataFeed\Widget\RsrUpdateWidget' );
	}
}