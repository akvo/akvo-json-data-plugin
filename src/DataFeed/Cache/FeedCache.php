<?php

/**
 * Cache interface for feed items.
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

/**
 * Store interface for data feed handles.
 */
interface FeedCache
{

	/**
	 * Get the currently cached item.  If no item newer than the given
	 * fetch interval is available, a new item will be fetched.
	 *
	 * @param string $feedName The name of the feed.
	 * @param string $url The url of the feed.
	 * @param int $interval The fetch interval.
	 *
	 * @return Associative array with the decoded data item.
	 *
	 * @throws FeedCacheException if no item could be fetched.
	 */
	function getCurrentItem($feedName, $url, $interval);


	/**
	 * Flush the named item from the cache.
	 *
	 * @param string $feedName The name of the feed.
	 */
	function flush($feedName);

}