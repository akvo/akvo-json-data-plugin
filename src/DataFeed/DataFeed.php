<?php

namespace DataFeed;

/**
 * Static class for wiring the plugin components and exposing the API methods 'item' and 'handle'.
 */
class DataFeed
{

	/**
	 * Component key for the feed store.
	 */
	const FEED_STORE = 'feed_store';

	/**
	 * Component key for the feed cache.
	 */
	const FEED_CACHE = 'feed_cache';

	/**
	 * Component key for the feed handle factory.
	 */
	const FEED_HANDLE_FACTORY = 'feed_handle_factory';

	/**
	 * Component key for the feed cache backend.
	 */
	const FEED_CACHE_BACKEND = 'feed_cache_backend';

	/**
	 * Component key for object query language.
	 */
	const OBJECT_QUERY_LANGUAGE = 'object_query_language';

	/**
	 * Component key for restful service.
	 */
	const REST_SERVICE = 'rest_service';


	/**
	 * Component key for object cache.
	 */
	const OBJECT_CACHE = 'object_cache';


	/**
	 * Component key for transent cache.
	 */
	const TRANSIENT_CACHE = 'transient_cache';

	/**
	 * Component key for the request data fetcher.
	 */
	const REQUEST_DATA_FETCHER = 'request_data_fetcher';

	private static $container = null;

	private static function configure()
	{
		$container = new \Pimple\Container( array(
				DataFeed::FEED_STORE => function ( $c ) {
					global $wpdb;
					return new \DataFeed\Store\DatabaseFeedStore( $wpdb, $c[DataFeed::FEED_HANDLE_FACTORY] );
				},
				DataFeed::FEED_CACHE_BACKEND => function ( $c ) {
					if (function_exists('\curl_init')) {
						return new \DataFeed\Cache\CurlFeedCache();
					}
					return new \DataFeed\Cache\FileGetContentsFeedCache();
				},
				DataFeed::FEED_CACHE => function ( $c ) {
					return new \DataFeed\Cache\TransientFeedCache( $c[DataFeed::FEED_CACHE_BACKEND], $c[DataFeed::TRANSIENT_CACHE] );
				},
				DataFeed::FEED_HANDLE_FACTORY => function ( $c ) {
					return new \DataFeed\Internal\DefaultFeedHandleFactory( $c[DataFeed::FEED_CACHE] );
				},
				DataFeed::OBJECT_QUERY_LANGUAGE => function( $c ) {
					return new \DataFeed\ObjectQuery\SimpleObjectQueryLanguage();
				},
				DataFeed::REST_SERVICE => function( $c ) {
					return new \DataFeed\Ajax\DefaultRestService( $c[DataFeed::FEED_HANDLE_FACTORY], $c[DataFeed::FEED_CACHE], $c[DataFeed::REQUEST_DATA_FETCHER] );
				},
				DataFeed::REQUEST_DATA_FETCHER => function( $c ) {
					return new \DataFeed\Ajax\DefaultRequestDataFetcher();
				},
				DataFeed::OBJECT_CACHE => function( $c ) {
					return new \DataFeed\Cache\ObjectCache();
				},
				DataFeed::TRANSIENT_CACHE => function( $c ) {
					return new \DataFeed\Cache\TransientCache();
				},
			)
		);

		$container[self::FEED_HANDLE_FACTORY]->setFeedStore( $container[self::FEED_STORE] );

		self::$container = $container;
	}

	static function component( $componentName )
	{
		if ( self::$container === null ) {
			self::configure();
		}

		return self::$container[$componentName];
	}

	/**
	 * @param string $name     The name of the feed.
	 * @param string $url      The url of the feed.  If the feed handle already exists, the default will be whatever is stored in the database.
	 * @param int    $interval The fetch interval in seconds.  The default interval is 24h.
	 *
	 * @return FeedHandle which can be used to fetch the current item of the feed using the method getCurrentItem().
	 *
	 * @throws DataFeed\NonexistingFeedException if the url is omitted and the feed doesn't already exist.
	 */
	public static function handle( $name, $url = null, $interval = 86400 )
	{
		$handle = self::component('feed_handle_factory')->create( $name, $url, $interval );
		if ( $handle->load() === \DataFeed\Store\FeedStore::LOAD_RESULT_NONEXISTING ) {
			$handle->store();
		}
		return $handle;
	}

	/**
	 * @param string $name     The name of the feed.
	 * @param string $url      The url of the feed.  If the feed handle already exists, the default will be whatever is stored in the database.
	 * @param int    $interval The fetch interval in seconds.  The default interval is 24h.
	 *
	 * @return object The current item of the feed as an object.
	 * WARNING: the contents of the item is untrusted data, no
	 * validation or escaping has been made on the fields by the data
	 * feed plugin.
	 *
	 * @throws DataFeed\NonexistingFeedException if the url is omitted and the feed doesn't already exist.
	 */
	public static function item( $name, $url = null, $interval = 86400 )
	{
		return self::handle( $name, $url, $interval )->getCurrentItem();
	}


	/**
	 * Short code hook.
	 *
	 * @param array $atts Passed attributes.
	 *
	 * @return string A string suitable for inserting in html context.
	 * The string either contains the value or an error message
	 * surrounded by the tags &lt;span class="data-feed-error"&gt; ...
	 * &lt;/span&gt;.
	 */
	public static function shortcode( $atts )
	{
		$errmsg = self::validate_shortcode_attributes( $atts );
		if ($errmsg !== null) {
			return '<span class="data-feed-error">' . $errmsg . '</span>';
		}

		try {
			if ( $atts['interval'] !== null ) {
				$item = self::item( $atts['name'], $atts['url'], $atts['interval'] );
			} else {
				$item = self::item( $atts['name'], $atts['url'] );
			}
			if ( $atts['query'] !== null ) {
				$ql = self::component( self::OBJECT_QUERY_LANGUAGE );
				return esc_html( $ql->query( $atts['query'], $item ) );
			}
			return esc_html( "$item" );
		} catch (\Exception $e) {
			return '<span class="data-feed-error">' . \esc_html( "$e" ) . '</span>';
		}

	}

	private static function validate_shortcode_attributes( &$atts )
	{
		$a = \shortcode_atts( array(
				'name'     => null,
				'url'      => null,
				'interval' => null,
				'query'    => null,
			), $atts );

		unset($atts[0]);

		$count = 0;
		foreach ( $a as $key => $val ) {
			if ($val !== null) {
				$count++;
			}
		}

		if (count($atts) !== $count ) {
			return "Invalid parameters present.  Valid parameters are 'name', 'url', 'interval', and 'query'.";
		}

		if ($a['name'] === null) {
			return "The parameter 'name' is mandatory.";
		}

		if ($a['url'] !== null) {
			/*
			 * Major WTF in "formatting.php:
			 *
			 * 		// Replace each & with &#038; unless it already looks like an entity.
			 *      $curl = preg_replace('/&([^#])(?![a-zA-Z1-4]{1,8};)/', '&#038;$1', $curl);
			 *
			 * Obviously, this is impossible to reverse.  Since the
			 * parameter value is destroyed, we will not be able to
			 * suppor URLs with URL-escaped query parameters.  Let's
			 * do the best of the situation and at least allow some
			 * query parameters in the url.
			 */
			$a['url'] = preg_replace( '/&#038;/', '&', $a['url'] );
		}

		if ($a['interval'] !== null) {
			if ( ! \preg_match( '/^[0-9]+$/', $a['interval'] ) ) {
				return "The parameter 'interval' must be an integer value.";
			}
			$a['interval'] = \intval($a['interval']);
		}

		$atts = $a;

	}

}

