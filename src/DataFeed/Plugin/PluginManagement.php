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

	public static function plugins_loaded()
	{
		\load_plugin_textdomain( 'data-feed', false, DATA_FEED_PLUGIN_DIR . '/i18n' );
	}
}