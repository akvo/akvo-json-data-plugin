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

class TransientFeedCache implements FeedCache
{

	/**
	 * The next level cache.
	 *
	 * @var FeedCache nextLevel
	 */
	private $nextLevel;

	public function __construct( FeedCache $nextLevel )
	{
		$this->nextLevel = $nextLevel
	}

	/**
	 * @override FeedCache::getCurrentItem
	 */
	public function getCurrentItem($feedName, $url, $interval)
	{
		$transientName = $this->getTransientName( $feedName );
		$value = \get_transient( $transientName );
		if ( $value !== false ) {
			return $value;
		}

		$value = $nextLevel->getCurrentItem();

		if ($value === false) {
			throw new FeedCacheException( "Upstream returned false on feed '$feedName'" );
		}

		\set_transient( $transientName, $value, $interval );

		return $value;

	}

	private function getTransientName( $feedName )
	{
		return 'transient-feed-cache-' . $feedName;
	}
}