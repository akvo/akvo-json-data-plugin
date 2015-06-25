<?php

namespace DataFeed\Pagination;

/**
 * Component for determining the URL of a particular page in a feed.
 */
interface PageUrl
{

	const PAGE_URL_ARRAY = 'page_url';

	/**
	 * Determine the URL for the next page in a paginated feed.
	 *
	 * The caller must call this function starting from the first page
	 * in the page set in order, starting with the index 0 and
	 * increasing the index by 1.  This function will maintain the
	 * field PAGE_URL_ARRAY in the metadata.
	 * 
	 * @param array  $meta Metadata about the feed.
	 * @param string $url  The main URL of the feed.
	 * @param mixed  $item The previous item in the feed.
	 * @param int    $page The page number.
	 *
	 * @return boolean False if the page URL is unchanged, true if the
	 * page URL was updated.
	 *
	 * If the page URL was changed, the URL of the following page must
	 * be rechecked.  If the URL of the following item has changed,
	 * the corresponding page must be flushed from the cache.
	 */
	function pageUrl( &$meta, $url, $item, $page );

}