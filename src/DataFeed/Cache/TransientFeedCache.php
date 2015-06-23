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

		$now = \time();

		\set_transient( $transientName,
			array(
				'item' => $value,
				'timestamp' => $now,
				'fetching' => 0,
				'url' => $url,
			), $interval + ((self::FETCH_RETRIES + 1) * self::FETCH_RETRY_INTERVAL) );

		return $value;
	}

	private static function shouldRefetch( $transient, $interval, $url )
	{
		$now = \time();

		$item_expired = (isset($transient['item']) && $now > $transient['timestamp'] + $interval) || ! isset($transient['item']);

		$failed_fetch_expired = ! isset($transient['last_attempt']) || $now > $transient['last_attempt'] + (self::FETCH_RETRY_INTERVAL * $transient['fetching']);

		return $item_expired && $failed_fetch_expired || $transient['url'] != $url;
	}

	/**
	 * @override FeedCache::getCurrentItem
	 */
	public function getCurrentItem( $feedName, $url, $interval )
	{
		$transientName = $this->getTransientName( $feedName );
		$transient = \get_transient( $transientName );
		if ( $transient !== false ) {
			if (self::shouldRefetch( $transient, $interval, $url ) ) {
				/*
				 * We use this simple scheme based on the 'fetching'
				 * counter to avoid fetching the same data several
				 * times simultaneously.  There is a race condition
				 * here, so the refetch could be performed several
				 * times anyway, but this should rarely happen, and
				 * the consequence is insignificant.  Proper
				 * synchronization is currently difficult in PHP.
				 */
				if ($transient['url'] == $url) {
					$transient['fetching']++;
				} else {
					/*
					 * Different URL passed, reset retry timeout.
					 */
					$transient['url'] = $url;
					$transient['fetching'] = 1;
					$transient['last_attempt'] = time();
				}
				\set_transient( $transientName, $transient );
				return $this->fetch( $feedName, $url, $interval, $transientName );
			}
			if ( ! isset( $transient['item'] ) ) {
				throw new FeedCacheException('Fetching of data feed item is in progress, but no current item is available.  Waiting for fetch retry interval (' .
					self::FETCH_RETRY_INTERVAL . 's) to expire.');
			}
			return $transient['item'];
		} else {
			/*
			 * Indicate that a fetch has been iniated, so it will not
			 * be retried until FETCH_RETRY_INTERVAL have expired or
			 * the url has changed, even if the fetch fails..
			 */
			\set_transient( $transientName,
				array(
					'last_attempt' => time(),
					'fetching' => 1,
					'url' => $url
				) );
		}

		return $this->fetch( $feedName, $url, $interval, $transientName );

	}

	private function getTransientName( $feedName )
	{
		return 'transient-feed-cache-' . $feedName;
	}

	public function flush( $feedName )
	{
		$transientName = $this->getTransientName( $feedName );
		\delete_transient( $transientName );
		$this->nextLevel->flush( $feedName );
	}
}