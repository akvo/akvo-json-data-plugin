<?php

namespace DataFeed\Pagination;

class NullPageUpdateCheck implements PageUpdateCheck
{

	/**
	 * Check for updated pages and update the timestamps in the metadata.
	 *
	 * This implementation always indicates that all pages have been updated.
	 *
	 * @param array $meta The metadata.
	 * @param mixed $item The currently known item.
	 *
	 * @return array of all page indicies.
	 */
	function checkUpdates( &$meta, $item )
	{
		if (!isset($meta[PageUrl::PAGE_URL_ARRAY])) {
			return array();
		}
		return range(0, count($meta[PageUrl::PAGE_URL_ARRAY]) - 1);
	}

}