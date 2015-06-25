<?php

/**
 * Cache feed items by merging items from a paginated feed.
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
 * @since      File available since Release 1.2
 */

namespace DataFeed\Cache;

use DataFeed\Cache\FeedCache;
use DataFeed\ObjectMerge\ObjectMerge;
use DataFeed\Pagination\PageUrl;
use DataFeed\Pagination\PageUpdateCheck;

class MergingFeedCache extends AbstractFeedCache
{

	/**
	 * var FeedCache the next layer in the cache stack.
	 */
	private $nextLevel;

	/**
	 * var ObjectMerge A merger component.
	 */
	private $merger;

	/**
	 * var string The name of the field in the chain of pages.  (Default 'next'.)
	 */
	private $nextField = 'next';

	/**
	 * var Cache a cache instance.
	 */
	private $cache;

	/**
	 * var PageUrl page url resolver.
	 */
	private $pageUrl;

	/**
	 * var PageUpdateCheck page update checker.
	 */
	private $pageUpdateCheck;

	public function __construct( FeedCache $next, Cache $cache, ObjectMerge $merger, PageUrl $pageUrl, PageUpdateCheck $pageUpdateCheck )
	{
		parent::__construct( $next, $cache );
		$this->merger = $merger;
		$this->pageUrl = $pageUrl;
		$this->pageUpdateCheck = $pageUpdateCheck;
	}

	protected function fetch( $feedName, $url, $interval, $ttl, &$meta )
	{
		if ($meta === false) {
			$meta = array();
		}
	}

	protected function getCacheKey( $feedName )
	{
		return 'merging-cache-' . $feedName;
	}

	private function subFeedName( $feedName, $page )
	{
		return $feedName . ':' . $page;
	}

	/**
	 * Fetch the given page.
	 *
	 * @param string $feedName
	 * @param string $url
	 * @param int $interval
	 * @param int $ttl
	 * @param array &$meta
	 * @param mixed $prevPage  The previos page, if available, otherwise null.  An exception might be thrown if null is passed, $pageNumber != 0 and no previous url has been cached for the page.
	 * @param int $pageNumber The page number (0 is the first page).
	 * @param mixed &$page  The actual page object will be stored in this variable if, a page is fetched.
	 * @param boolean $onlyOnNewUrl If set, the page will be fetched only if the url of the page have changed.
	 *
	 * @return true if a page was fetched, otherwise false.
	 *
	 * @throws PageUrlFailedException if the page url resolution mechanism failed.
	 */
	private function fetchPage( $feedName, $url, $interval, $ttl, &$meta, $prevPage, $pageNumber, &$page, $onlyOnNewUrl )
	{
		$page = null;

		$pageUrlUpdated = $this->pageUrl->pageUrl( $meta, $url, $prevPage, $pageNumber );
		if ($onlyOnNewUrl && !$pageUrlUpdated) {
			return false;
		}

		if ( ! empty($meta[PageUrl::PAGE_URL_ARRAY][$pageNumber]) ) {
			$pageUrl = $meta[PageUrl::PAGE_URL_ARRAY][$pageNumber];
			$page = $this->nextLevel->getCurrentItem( $this->subFeedName( $feedName, $pageNumber ), $pageUrl, $interval, $ttl );
			return true;
		}

		return false;
	}

}