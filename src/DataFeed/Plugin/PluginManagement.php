<?php

namespace DataFeed\Plugin;

class PluginManagement
{

	public static function activation()
	{
		foreach ( self::$actions as $a ) {
			error_log( "adding action $a as call to " . __CLASS__ . '::' . $a );
			\add_action( $a, array( __CLASS__, $a ) );
		}
	}

	public static function deactivation()
	{
		foreach ( self::$actions as $a ) {
			error_log( "removing action $a as call to " . __CLASS__ . '::' . $a );
			\remove_action( $a, array( __CLASS__, $a ) );
		}
	}

	public static function uninstall()
	{
	}

	public static function admin_menu()
	{
		error_log('Adding admin menu.');
		\add_options_page( __('Data feeds', 'data-feed'), __('Data feeds\' options', 'data-feed'), 'manage_options', 'data-feed-options', __NAMESPACE__ . '\OptionsPage::page');
	}

	public static function plugins_loaded()
	{
		error_log('Loading i18n texts.');
		\load_plugin_textdomain( 'data-feed', false, DATA_FEED_PLUGIN_DIR . '/i18n' );
	}
}