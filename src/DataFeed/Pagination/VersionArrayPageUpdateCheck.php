<?php

namespace DataFeed\Pagination;

class VersionArrayPageUpdateCheck implements PageUpdateCheck
{

	private $fieldName;

	public function __construct( $fieldName = 'page_versions' )
	{
		if ( is_string( $fieldName ) ) {
			$this->fieldName = $fieldName;
		} else {
			$this->fieldName = 'page_versions';
		}
	}

	/**
	 * Check for updated pages and update the timestamps in the metadata.
	 *
	 * This implementation always indicates that all pages have been updated.
	 *
	 * @param array $meta The metadata.
	 * @param mixed $item The first page
	 *
	 * @return array of all page indicies.
	 */
	function checkUpdates( &$meta, $firstPage )
	{

		if (is_array($firstPage)) {
			if (isset( $firstPage[$this->fieldName] ) && is_array( $firstPage[$this->fieldName] ) ) {
				$new = $firstPage[$this->fieldName];
			} else {
				$new = array();
			}
		} else if (is_object($firstPage)) {
			if (isset( $firstPage->{$this->fieldName} ) && is_array( $firstPage->{$this->fieldName} ) ) {
				$new = $firstPage->{$this->fieldName};
			} else {
				$new = array();
			}
		} else {
			$new = array();
		}

		if (isset( $meta[self::PAGE_UPDATES] ) && is_array( $meta[self::PAGE_UPDATES] ) ) {
			$old = $meta[self::PAGE_UPDATES];
		} else {
			$old = array();
		}

		$updates = array();

		for ($i = 0; $i < count( $new ) || $i < count( $old ); $i++ ) {
			if ( ! (isset( $new[$i] ) && isset( $old[$i] ) && $new[$i] == $old[$i]) ) {
				$updates[] = $i;
			}
		}

		$meta[self::PAGE_UPDATES] = $new;

		return $updates;
	}
}