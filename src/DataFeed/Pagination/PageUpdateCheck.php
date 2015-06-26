<?php

namespace DataFeed\Pagination;

/**
 * Component for checking which pages are updated in a paginated feed.
 */
interface PageUpdateCheck
{
	/**
	 * Index in the meta array.
	 */
	const PAGE_UPDATES = 'page_updates';

	/**
	 * Check for updated pages and update the timestamps in the metadata.
	 *
	 * This method may store an array of timestamps or version numbers
	 * in the meta array at index PAGE_UPDATES.
	 *
	 * @param array $meta The metadata.
	 * @param mixed $firstPage The current first page.
	 *
	 * @return array of page indicies that have been updated.
	 */
	function checkUpdates( &$meta, $item );

}