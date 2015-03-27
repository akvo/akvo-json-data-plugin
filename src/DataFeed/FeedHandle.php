<?php
/**
 * The handle for data feeds.
 *
 * The FeedHandle class is the core class of the DataStream package.  Instances contains the settings for the data feed.
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

namespace DataFeed;

use DataFeed\Store\FeedStore;
use DataFeed\Cache\FeedCache;

/**
 * The feed handle contains the configuration for an instance of a data feed.
 */
class FeedHandle
{

	/**
	 * The name of the feed.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * The URL where the data is fetched.
	 *
	 * @var string
	 */
	private $url;

	/**
	 * URL overridden through admin interface.
	 *
	 * @var string
	 */
	private $o_url;

	/**
	 * The fetch interval in seconds.  Default: 60 * 24  (24h).
	 *
	 * @var int
	 */
	private $interval;

	/**
	 * Fetch interval overridden through admin interface.
	 *
	 * @var string
	 */
	private $o_interval;

	/**
	 * Injected feed store.
	 *
	 * @var FeedStore.
	 */
	private $feed_store;

	/**
	 * Injected feed item cache.
	 *
	 * @var FeedCache
	 */
	private $feed_item_cache;

	/**
	 * Construct a data feed handle.
	 *
	 * @param FeedStore $feed_store The feed store.
	 * @param string $name     The name of the feed.
	 * @param string $url      The url of the feed.
	 * @param int    $interval The fetch interval in seconds.
	 */
	public function __construct( FeedStore $feed_store, FeedCache $feed_item_cache, $name, $url = NULL, $interval = 1440 )
	{
		$this->name = $name;
		$this->url = $url;
		$this->interval = $interval;
		$this->feed_store = $feed_store;
		$this->feed_item_cache = $feed_item_cache;
	}

	/**
	 * @return string The name of the feed.
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string The URL in effect of this feed.
	 */
	public function getEffectiveURL()
	{
		if (isset($this->o_url) ) {
			return $this->o_url;
		}

		return $this->url;
	}

	/**
	 * @return string The URL of this feed.
	 */
	public function getURL()
	{
		return $this->url;
	}


	/**
	 * @param string $url The URL to load items from.
	 */
	public function setURL($url)
	{
		$this->url = $url;
	}

	/**
	 * @param string $url The override URL.
	 */
	public function setOURL($url)
	{
		$this->o_url = $url;
	}

	/**
	 * @return int The effective fetch interval of this feed.
	 */
	public function getEffectiveInterval()
	{
		if (isset($this->o_interval) ) {
			return $this->o_interval;
		}

		return $this->interval;
	}

	/**
	 * @return int The fetch interval of this feed.
	 */
	public function getInterval()
	{
		return $this->interval;
	}

	/**
	 * @param int $interval The feed interval in seconds.
	 */
	public function setInterval($interval)
	{
		$this->interval = $interval;
	}

	/**
	 * @param int $interval The override feed interval in seconds.
	 */
	public function setOInterval($interval)
	{
		$this->o_interval = $interval;
	}

	/**
	 * @return DateTime the creation time of this feed handle.
	 */
	public function getCreated()
	{
		return $this->created;
	}

	/**
	 * @param DateTime $created the creation time of the feed.
	 */
	public function setCreated( $created )
	{
		$this->created = $created;
	}

	/**
	 * Store the feed handle in persistent storage.
	 */
	public function store()
	{
		$this->feed_store->store( $this );
	}

	/**
	 * Obtain the current item.
	 *
	 * @return Associative array with the decoded data item.
	 */
	public function getCurrentItem()
	{
		return $feed_item_cache->getItem($this->getName(), $this->getEffectiveURL(), $this->getEffectiveInterval());
	}
}