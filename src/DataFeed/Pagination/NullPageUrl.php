<?php

namespace DataFeed\Pagination;

/**
 * A page url selector that doesn't support pagination at all.
 */
class NullPageUrl implements PageUrl
{

	/**
	 * Will set the main url of the feed for page 0, and null for any other page.
	 */
	function pageUrl( &$meta, $url, $item, $page )
	{
		if ( $page === 0 ) {
			if ( isset( $meta[self::PAGE_URL_ARRAY] ) && isset( $meta[self::PAGE_URL_ARRAY][0] )) {
				if ($meta[self::PAGE_URL_ARRAY][0] != $url) {
					$meta[self::PAGE_URL_ARRAY][0] = $url;
					return true;
				}
				return false;
			}
			$meta[self::PAGE_URL_ARRAY] = array( $url );
			return true;
		} 
		return false;
	}
	
}