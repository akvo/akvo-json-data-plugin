<?php

/**
 * Cache feed items using the wordpress transient database api.
 *
 * PHP version 5.3
 *
 * @category   Wordpress plugin
 * @package    Data Feed
 * @author     Andreas Jonsson <andreas.jonsson@kreablo.se>
 * @copyright  2015 Andreas Jonsson
 * @license    GNU AFFERO GENERAL PUBLIC LICENSE version 3 http://www.gnu.org/licenses/agpl.html
 * @version    Git: $Id$
 * @link       https://github.com/akvo/akvo-json-data-plugin
 * @since      File available since Release 1.0
 */

namespace DataFeed\Cache;

use DataFeed\Cache\FeedCache;

class TransientFeedCache implements FeedCache
{

	/**
	 * To prevent multiple requests triggering simultaneous fetching, we trigger a fetch before the cached item expires and mark the cached item that a fetch is in progress.
	 */
	const FETCH_RETRY_INTERVAL = 30;
	const FETCH_RETRIES = 2;

	/**
	 * The next level cache.
	 *
	 * @var FeedCache nextLevel
	 */
	private $nextLevel;

	public function __construct( FeedCache $nextLevel )
	{
		$this->nextLevel = $nextLevel;
	}

	private function fetch( $feedName, $url, $interval, $transientName)
	{
		$value = $this->nextLevel->getCurrentItem( $feedName, $url, $interval );

		if ($value === false) {
			throw new FeedCacheException( "Upstream returned false on feed '$feedName'" );
		}

		\set_transient( $transientName,
			array(
				'item' => $value,
				'timestamp' => time(),
				'fetching' => 0,
			), $interval + ((self::FETCH_RETRIES + 1) * self::FETCH_RETRY_INTERVAL) );

		return $value;
	}

	private static function shouldRefetch( $transient, $interval )
	{
		return time() > $transient['timestamp'] + $interval + ( $transient['fetching'] * self::FETCH_RETRY_INTERVAL );
	}

	/**
	 * @override FeedCache::getCurrentItem
	 */
	public function getCurrentItem( $feedName, $url, $interval )
	{
		$transientName = $this->getTransientName( $feedName );
		$transient = \get_transient( $transientName );
		if ( $transient !== false ) {
			if (self::shouldRefetch( $transient, $interval ) ) {
				/*
				 * We use this simple scheme based on the 'fetching'
				 * counter to avoid fetching the same data several
				 * times simultaneously.  There is a race condition
				 * here, so the refetch could be performed several
				 * times anyway, but this should rarely happen, and
				 * the consequence is insignificant.  Proper
				 * synchronization is currently difficult in PHP.
				 */
				$transient['fetching']++;
				\set_transient( $transientName, $transient );
				return $this->fetch( $feedName, $url, $interval, $transientName );
			}
			return $transient['item'];
		}

		return $this->fetch( $feedName, $url, $interval, $transientName );

	}

	private function getTransientName( $feedName )
	{
		return 'transient-feed-cache-' . $feedName;
	}
}