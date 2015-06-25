<?php

namespace DataFeed\Cache;

/**
 * Basic caching mechanism.
 */
interface Cache
{

	/**
	 * Set a value in the cache.
	 *
	 * @param string $key The key for the item.
	 * @param mixed $value The value.
	 * @param int $ttl The time to live for the item in seconds.
	 */
	function set( $key, $value, $ttl );

	/**
	 * Get a value from the cache.
	 *
	 * @param string $key The key for the item.
	 * @return mixed The value.
	 */
	function get( $key );

	/**
	 * Delete a value in the cache.
	 *
	 * @param string $key The key for the item.
	 */
	function delete( $key );

}