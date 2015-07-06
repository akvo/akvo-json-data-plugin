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
use DataFeed\DataFeed;
use DataFeed\Pagination\PageUrlFactory;
use DataFeed\Pagination\PageUpdateCheckFactory;

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
	 * API key to add to the effective URL as query parameter.
	 *
	 * @var string
	 */
	private $key;

	/**
	 * Query parameter name to add the API key to.  ('key' if unset).
	 *
	 * @var string
	 */
	private $key_parameter;

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
	 * Pagination policy.
	 *
	 * @var string String describing pagination policy.  Format: ('page_url=<page url component name>[:page url component parameter]&page_update_check=<page update check component name>[:page update check parameter]');
	 */
	private $pagination_policy;

	/**
	 * Overriden pagination policy;
	 */
	private $o_pagination_policy;

	/**
	 * Injected page url factory.
	 */
	private $page_url_factory;

	/**
	 * Injected page_update_check_factory.
	 */
	private $page_update_check_factory;

	/**
	 * Construct a data feed handle.
	 *
	 * @param FeedStore $feed_store The feed store.
	 * @param FeedCache $feed_item_cache The cache to fetch items from.
	 * @param PageUrlFactory $page_url_factory The page url resolver factory.
	 * @param PageUpdateCheckFactory $page_update_check_factory The page update checker factory.
	 * @param string $name     The name of the feed.
	 * @param string $url      The url of the feed.
	 * @param int    $interval The fetch interval in seconds.
	 */
	public function __construct(
		FeedStore $feed_store,
		FeedCache $feed_item_cache,
		PageUrlFactory $page_url_factory,
		PageUpdateCheckFactory $page_update_check_factory,
		$name,
		$url = null,
		$interval = null,
		$pagination_policy = null
	)
	{
		$this->name = $name;
		$this->url = $url;
		$this->interval = $interval;
		$this->feed_store = $feed_store;
		$this->feed_item_cache = $feed_item_cache;
		$this->page_url_factory = $page_url_factory;
		$this->page_update_check_factory = $page_update_check_factory;
		$this->setPaginationPolicy( $pagination_policy );
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
		if (!empty($this->o_url) ) {
			$eUrl = $this->o_url;
		} else {
			$eUrl = $this->url;
		}

		$key = $this->getKey();
		if ( !empty($key) ) {
			$parts = \parse_url( $eUrl );

			$query = array();

			if ( !empty( $parts['query'] ) ) {
				\parse_str( $parts['query'], $query );
			}

			$parameter = $this->getKeyParameter();
			if ( empty($parameter) ) {
				$parameter = 'key';
			} else {
				$parameter = $this->getKeyParameter();
			}

			$query[$parameter] = $key;

			$parts['query'] = \http_build_query( $query );

			$eUrl = DataFeed::build_url( $parts );
		}

		return $eUrl;
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
	 * @return string The override URL.
	 */
	public function getOURL()
	{
		return $this->o_url;
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
		if ($interval == null) {
			$this->interval = null;
		} else {
			$this->interval = (int) $interval;
		}
	}

	/**
	 * @param int $interval The override feed interval in seconds.
	 */
	public function setOInterval($interval)
	{
		if ($interval == null) {
			$this->o_interval = null;
		} else {
			$this->o_interval = (int) $interval;
		}
	}

	/**
	 * @return int The override interval.
	 */
	public function getOInterval()
	{
		return $this->o_interval;
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
	 * @param string $key the API key.
	 */
	public function setKey( $key )
	{
		$this->key = $key;
	}


	/**
	 * @return string $key the API key.
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * Set the pagination policy.
	 *
	 * @param string $pagination_policy description of pagination policy.
	 */
	public function setPaginationPolicy( $pagination_policy )
	{
		$this->pagination_policy = $pagination_policy;
		if ( ! isset( $this->o_pagination_policy ) ) {
			$this->configurePaginationPolicy( $pagination_policy );
		}
	}

	/**
	 * @return the pagination policy description.
	 */
	public function getPaginationPolicy()
	{
		return $this->pagination_policy;
	}

	/**
	 * Set the override pagination policy.
	 *
	 * @param string $o_pagination_policy description of pagination policy.
	 */
	public function setOPaginationPolicy( $o_pagination_policy )
	{
		$this->o_pagination_policy = $o_pagination_policy;
		if ( empty( $o_pagination_policy ) ) {
			$this->configurePaginationPolicy( $this->getPaginationPolicy() );
		} else {
			$this->configurePaginationPolicy( $o_pagination_policy );
		}
	}

	/**
	 * @return the override pagination policy description.
	 */
	public function getOPaginationPolicy()
	{
		return $this->o_pagination_policy;
	}

	/**
	 * @param string $key_parameter the query parameter to use for the API key.
	 */
	public function setKeyParameter( $key_parameter )
	{
		$this->key_parameter = $key_parameter;
	}

	/**
	 * @return string the query parameter to use for the API key.
	 */
	public function getKeyParameter()
	{
		return $this->key_parameter;
	}

	/**
	 * Store the feed handle in persistent storage.
	 */
	public function store()
	{
		$this->feed_store->storeFeedHandle( $this );
	}


	/**
	 * Load the feed handle from persistent storage.
	 *
	 * @return LOAD_RESULT_CLEAN       if the configuration of the passed feed matches the existing feed in the store.
	 *         LOAD_RESULT_DIRTY       if the configuration of the passed feed doesn't match the existing feed in the store.
	 *         LOAD_RESULT_NONEXISTING if the feed didn't exist in the store.
	 *
	 * @throws NonexistingFeedException if the url is not set and the handle doesn't exist in the database.
	 */
	public function load()
	{
		$ret = $this->feed_store->loadFeedHandle( $this );
		if ( $ret === FeedStore::LOAD_RESULT_NONEXISTING && $this->getURL() === null ) {
			throw new NonexistingFeedException( $this );
		}
		return $ret;
	}

	/**
	 * Removes the feed handle from persistent storage.
	 */
	public function remove()
	{
		$this->feed_item_cache->flush( $this->getName() );
		$this->feed_store->removeFeedHandle( $this );
	}

	/**
	 * Obtain the current item.
	 *
	 * @return Associative array with the decoded data item.
	 */
	public function getCurrentItem()
	{
		return $this->feed_item_cache->getCurrentItem($this->getName(), $this->getEffectiveURL(), $this->getEffectiveInterval());
	}


	/**
	 * Return an associative array representation of this handle.
	 *
	 * @return Associative array.
	 */
	public function asArray()
	{

		return array(
			'name'       => $this->getName(),
			'url'        => $this->getURL(),
			'o_url'      => $this->getOURL(),
			'interval'   => $this->getInterval(),
			'o_interval' => $this->getOInterval(),
			'key'        => $this->getKey(),
			'key_parameter' => $this->getKeyParameter(),
			'pagination_policy' => $this->getPaginationPolicy(),
			'o_pagination_policy' => $this->getOPaginationPolicy()
		);
	}


	private function configurePaginationPolicy( $pagination_policy )
	{
		if (empty( $pagination_policy ) ) {
			$this->feed_item_cache = DataFeed::component( DataFeed::FEED_CACHE );
			return;
		}

		$parameters = array();

		parse_str( $pagination_policy, $parameters );

		$page_url = null;

		if (isset($parameters['page-url'])) {
			$page_url = $parameters['page-url'];
		}
		$pageUrlComponent = $this->page_url_factory->create( $page_url );

		$page_update_check = null;
		if (isset($parameters['page-update-check'])) {
			$page_update_check = $parameters['page-update-check'];
		}
		$pageUpdateCheckComponent = $this->page_update_check_factory->create( $page_update_check );

		$this->feed_item_cache = DataFeed::component( DataFeed::MERGING_FEED_CACHE );

		$this->feed_item_cache->setPageUpdateCheck( $pageUpdateCheckComponent );
		$this->feed_item_cache->setPageUrl( $pageUrlComponent );

		if (isset($parameters['limit']) && \is_numeric($parameters['limit']) ) {
			$this->feed_item_cache->setLimit((int) $parameters['limit']);
		}
		
	}
}