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

	const PAGES_ARRAY = 'pages_array';

	/**
	 * var ObjectMerge A merger component.
	 */
	private $merger;

	/**
	 * var string The name of the field in the chain of pages.  (Default 'next'.)
	 */
	private $nextField = 'next';

	/**
	 * var PageUrl page url resolver.
	 */
	private $pageUrl;

	/**
	 * var PageUpdateCheck page update checker.
	 */
	private $pageUpdateCheck;

	public function __construct( FeedCache $nextLevel, Cache $cache, ObjectMerge $merger, PageUrl $pageUrl, PageUpdateCheck $pageUpdateCheck )
	{
		parent::__construct( $nextLevel, $cache );
		$this->merger = $merger;
		$this->pageUrl = $pageUrl;
		$this->pageUpdateCheck = $pageUpdateCheck;
	}

	protected function fetch( $feedName, $url, $interval, $ttl, &$meta )
	{
		if ($meta === false) {
			$meta = array();
		}

		if ( ! isset( $meta[self::PAGES_ARRAY] ) ) {
			$meta[self::PAGES_ARRAY] = array();
		}

		$item = array();
		$now = \time();
		$meta[self::URL_KEY] = $url;
		$meta[self::FETCHING_KEY] = 0;
		$meta[self::TIMESTAMP_KEY] = $now;

		$page = null;
		$this->fetchPage( $feedName, $url, $meta, null, 0, $page, false );

		$updates = $this->pageUpdateCheck->checkUpdates( $meta, $page );

		$nextUpdate = array_shift( $updates );
		if ($nextUpdate === 0) {
			$nextUpdate = array_shift( $updates );
		}

		$pageNumber = 1;
		do {
			$item = $this->merger->merge( $item, $page );

			$update = $nextUpdate === $pageNumber;
			if ($update) {
				$nextUpdate = array_shift( $updates );
			}
			$prevPage = $page;

			$fetchedSomething = $this->fetchPage( $feedName, $url, $meta, $prevPage, $pageNumber, $page, $update );
			$pageNumber++;

		} while ($fetchedSomething);

		for ($i = count($meta[self::PAGES_ARRAY]) - 1; $i >= $pageNumber ; $i-- ) {

			// The number of pages have decreased.

			$self->nextLevel->flush( $meta[self::PAGES_ARRAY][$i] );
			unset( $meta[self::PAGES_ARRAY][$i] );
		}

		$meta[self::ITEM_KEY] = $item;
			
		$this->cache->set( $this->getCacheKey( $feedName ), $meta, $ttl !== null ? $ttl : $interval );
		return $meta[self::ITEM_KEY];
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
	 * @param array &$meta
	 * @param mixed $prevPage  The previos page, if available, otherwise null.  An exception might be thrown if null is passed, $pageNumber != 0 and no previous url has been cached for the page.
	 * @param int $pageNumber The page number (0 is the first page).
	 * @param mixed &$page  The actual page object will be stored in this variable if, a page is fetched.
	 * @param boolean $doUpdate If set, a recent version of 
	 *
	 * @return true if a page was fetched, otherwise false.
	 *
	 * @throws PageUrlFailedException if the page url resolution mechanism failed.
	 */
	private function fetchPage( $feedName, $url, &$meta, $prevPage, $pageNumber, &$page, $doUpdate )
	{
		$page = null;

		$pageUrlUpdated = $this->pageUrl->pageUrl( $meta, $url, $prevPage, $pageNumber );
		$doUpdate = $doUpdate || $pageUrlUpdated;

		if ( ! empty($meta[PageUrl::PAGE_URL_ARRAY][$pageNumber]) ) {
			$pageUrl = $meta[PageUrl::PAGE_URL_ARRAY][$pageNumber];
			if ($doUpdate) {
				// Force refetch from next level by setting interval to 0.
				$interval = 0;
			} else {
				// Avoid refetch from next level by setting interval to 50 years.
				$interval = 1576800000;
			}

			$subFeed = $this->subFeedName( $feedName, $pageNumber );

			if ( !isset( $meta[self::PAGES_ARRAY][$pageNumber] ) || $meta[self::PAGES_ARRAY][$pageNumber] != $subFeed ) {
				$meta[self::PAGES_ARRAY][$pageNumber] = $subFeed;
			}

			$page = $this->nextLevel->getCurrentItem( $subFeed, $pageUrl, $interval, 0 );
			return true;
		}

		return false;
	}

}