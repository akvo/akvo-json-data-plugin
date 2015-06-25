<?php

namespace DataFeed\Pagination;

/**
 * A page url selector that supports pagination via a next url in the previous item.
 */
class NextPageUrl implements PageUrl
{

	/**
	 * The fieldname.
	 *
	 * @var string
	 */
	private $fieldName;

	public function __construct( $fieldName = 'next' )
	{
		$this->fieldName = $fieldName;
	}

	private function next( $item )
	{
		if (is_object($item)) {
			$url = isset($item->{$this->fieldName}) ? $item->{$this->fieldName} : null;
		} else if (is_array($item)) {
			$url = isset($item[$this->fieldName]) ? $item[$this->fieldName] : null;
		} else {
			$url = null;
		}
		if (empty( $url )) {
			return null;
		}
		return $url;
	}

	public function pageUrl( &$meta, $url, $prevPage, $page )
	{
		if ( $page == 0 ) {
			$nextUrl = $url;
		} else {
			if ( $prevPage === null ) {
				if (isset( $meta[self::PAGE_URL_ARRAY] ) && isset( $meta[self::PAGE_URL_ARRAY][$page] ) ) {
					return false;
				} else {
					throw new PageUrlFailureException('No previous page and no cached URL for page number ' + $page + ' of feed ' . $url . '!' );
				}
			}
			$nextUrl = $this->next( $prevPage );
		}

		if ( isset( $meta[self::PAGE_URL_ARRAY] ) && isset( $meta[self::PAGE_URL_ARRAY][$page] )) {

			if ( $nextUrl == null ) {
				array_splice( $meta[self::PAGE_URL_ARRAY], $page );
				return true;
			}
			
			if ( $nextUrl != $meta[self::PAGE_URL_ARRAY][$page] ) {
				$meta[self::PAGE_URL_ARRAY][$page] = $nextUrl;
				return true;
			}

			return false;
		}

		if ( $nextUrl == null) {
			return false;
		}

		if ( ! isset( $meta[self::PAGE_URL_ARRAY] ) ) {
			$meta[self::PAGE_URL_ARRAY] = array();
		}

		$meta[self::PAGE_URL_ARRAY][$page] = $nextUrl;
		return true;
	}
}
