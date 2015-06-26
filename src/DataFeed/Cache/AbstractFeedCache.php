<?php

namespace DataFeed\Cache;

abstract class AbstractFeedCache implements FeedCache
{
	/**
	 * To prevent multiple requests triggering simultaneous fetching, we trigger a fetch before the cached item expires and mark the cached item that a fetch is in progress.
	 */
	const FETCH_RETRY_INTERVAL = 30;
	const FETCH_RETRIES = 2;

	/**
	 * Key where the item is stored in the metadata.
	 */
	const ITEM_KEY = 'item';

	/**
	 * Key where the url is stored in the metadata.
	 */
	const URL_KEY = 'url';

	/**
	 * Key where the last attempt timestamp is stored in the metadata.
	 */
	const LAST_ATTEMPT_KEY = 'last_attempt';

	/**
	 * Key where the timestamp of the metadata is stored.
	 */
	const TIMESTAMP_KEY = 'timestamp';

	/**
	 * Key where the refetch counter is stored in the metadata.
	 */
	const FETCHING_KEY = 'fetching';


	/**
	 * Cache component to actually store the cached item.
	 */
	protected $cache;


	/**
	 * The next level cache.
	 *
	 * @var FeedCache nextLevel
	 */
	protected $nextLevel;

	protected function __construct( FeedCache $nextLevel, Cache $cache )
	{
		$this->cache = $cache;
		$this->nextLevel = $nextLevel;
	}

	abstract protected function fetch( $feedName, $url, $interval, $ttl, &$meta );

	abstract protected function getCacheKey( $feedName );

	protected static function shouldRefetch( $meta, $interval, $url )
	{
		$now = \time();

		$item_expired = (isset($meta[self::ITEM_KEY]) && $now > $meta[self::TIMESTAMP_KEY] + $interval) || ! isset($meta[self::ITEM_KEY]);

		$failed_fetch_expired = ! isset($meta[self::LAST_ATTEMPT_KEY]) || $now > $meta[self::LAST_ATTEMPT_KEY] + (self::FETCH_RETRY_INTERVAL * $meta[self::FETCHING_KEY]);

		return $item_expired && $failed_fetch_expired || $meta[self::URL_KEY] != $url;
	}

	private function maybeRefetch( $key, $meta, $interval, $url, $feedName, $ttl )
	{
		if (self::shouldRefetch( $meta, $interval, $url ) ) {
			/*
			 * We use this simple scheme based on the 'fetching'
			 * counter to avoid fetching the same data several
			 * times simultaneously.  There is a race condition
			 * here, so the refetch could be performed several
			 * times anyway, but this should rarely happen, and
			 * the consequence is insignificant.  Proper
			 * synchronization is currently difficult in PHP.
			 */
			if ($meta[self::URL_KEY] == $url) {
				$meta[self::FETCHING_KEY]++;
			} else {
				/*
				 * Different URL passed, reset retry timeout.
				 */
				$meta[self::URL_KEY] = $url;
				$meta[self::FETCHING_KEY] = 1;
				$meta[self::LAST_ATTEMPT_KEY] = \time();
			}
			$this->cache->set( $key, $meta, $ttl !== null ? $ttl : $interval );
			return $this->fetch( $feedName, $url, $interval, $ttl, $meta );
		}
		if ( ! isset( $meta[self::ITEM_KEY] ) ) {
			throw new FeedCacheException('Fetching of data feed item is in progress, but no current item is available.  Waiting for fetch retry interval (' .
				self::FETCH_RETRY_INTERVAL . 's) to expire.');
		}
		return $meta[self::ITEM_KEY];
	}

	/**
	 * @override FeedCache::getCurrentItem
	 */
	public function getCurrentItem( $feedName, $url, $interval, $ttl = null )
	{
		$key= $this->getCacheKey( $feedName );
		$meta = $this->cache->get( $key );
		if ( $meta !== false ) {
			return $this->maybeRefetch( $key, $meta, $interval, $url, $feedName, $ttl );
		} else {
			/*
			 * Indicate that a fetch has been iniated, so it will not
			 * be retried until FETCH_RETRY_INTERVAL have expired or
			 * the url has changed, even if the fetch fails..
			 */
			$this->cache->set( $key,
				array(
					self::LAST_ATTEMPT_KEY => time(),
					self::FETCHING_KEY => 1,
					self::URL_KEY => $url
				), self::FETCH_RETRY_INTERVAL );
		}

		return $this->fetch( $feedName, $url, $interval, $ttl, $meta );

	}

	public function flush( $feedName )
	{
		$this->cache->delete( $this->getCacheKey( $feedName ) );
		$this->nextLevel->flush( $feedName );
	}
}