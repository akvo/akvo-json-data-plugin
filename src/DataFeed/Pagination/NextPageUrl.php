<?php

namespace DataFeed\Pagination;

use DataFeed\DataFeed;

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
		if ( is_string( $fieldName ) ) {
			$this->fieldName = $fieldName;
		} else {
			$this->fieldName = 'next';
		}
	}

	private function next( $item, $mainUrl )
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

		$parts = parse_url( $url );
		if (isset($parts['scheme'])) {
			return $url;
		}
		$mainParts = parse_url( $mainUrl );
		$parts['scheme'] = $mainParts['scheme'];
		if (!isset($parts['host'])) {
			$parts['host'] = $mainParts['host'];
			if (!isset($parts['port'])) {
				$parts['port'] = $mainParts['port'];
			}
		}
		if (!isset($parts['path'])) {
			$parts['path'] = $mainParts['path'];
		}
		return DataFeed::build_url( $parts );
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
			$nextUrl = $this->next( $prevPage, $url );
		}

		if ( isset( $meta[self::PAGE_URL_ARRAY] ) && isset( $meta[self::PAGE_URL_ARRAY][$page] )) {

			if ( $nextUrl == null ) {
				array_splice( $meta[self::PAGE_URL_ARRAY], $page );
				return true;
			}
			
			if ( $nextUrl != $meta[self::PAGE_URL_ARRAY][$page] ) {

				// Loop detection
				for ($i = 0; $i < $page; $i++) {
					if ($meta[self::PAGE_URL_ARRAY][$i] == $nextUrl) {
						throw new PageUrlFailureException('Loop detected for url "' . $nextUrl . '" in paged data feed!');
					}
				}

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
