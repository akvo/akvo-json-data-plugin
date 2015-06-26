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

class TransientFeedCache extends AbstractFeedCache
{

	public function __construct( FeedCache $nextLevel, Cache $cache )
	{
		parent::__construct( $nextLevel, $cache );
	}

	protected function fetch( $feedName, $url, $interval, $ttl,  &$meta )
	{
		$value = $this->nextLevel->getCurrentItem( $feedName, $url, $interval, $ttl );

		if ($value === false) {
			throw new FeedCacheException( "Upstream returned false on feed '$feedName'" );
		}

		$now = \time();

		$this->cache->set( $this->getCacheKey( $feedName ),
			array(
				self::ITEM_KEY => $value,
				self::TIMESTAMP_KEY => $now,
				self::FETCHING_KEY => 0,
				self::URL_KEY => $url,
			), ($ttl !== null ? $ttl : $interval) + ((self::FETCH_RETRIES + 1) * self::FETCH_RETRY_INTERVAL) );

		return $value;
	}

	protected final function getCacheKey( $feedName )
	{
		return 'transient-feed-cache-' . $feedName;
	}

}