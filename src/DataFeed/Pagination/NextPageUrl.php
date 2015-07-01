<?php

namespace DataFeed\Pagination;

use DataFeed\DataFeed;
use DataFeed\ObjectQuery\ObjectQueryLanguage;

/**
 * A page url selector that supports pagination via a next url in the previous item.
 */
class NextPageUrl implements PageUrl
{

	/**
	 * The fieldname expression in the given object query language.
	 *
	 * @var string
	 */
	private $fieldExpr;

	/**
	 * Injected object query language.
	 *
	 * @var ObjectQueryLanguage
	 */
	private $objectQueryLanguage;

	public function __construct( ObjectQueryLanguage $objectQueryLanguage, $fieldExpr = 'meta->next' )
	{
		if ( is_string( $fieldExpr ) ) {
			$this->fieldExpr = $fieldExpr;
		} else {
			$this->fieldExpr = 'meta->next';
		}
		$this->objectQueryLanguage = $objectQueryLanguage;
	}

	private function next( $item, $mainUrl )
	{
		$url = $this->objectQueryLanguage->query( $this->fieldExpr, $item );

		if (empty( $url )) {
			return null;
		}

		$parts = parse_url( $url );
		if (isset($parts['scheme'])) {
			return $url;
		}
		$mainParts = parse_url( $mainUrl );
		$parts['scheme'] = $mainParts['scheme'];
		if (!isset($parts['host']) && isset( $mainParts['host']) ) {
			$parts['host'] = $mainParts['host'];
			if (!isset($parts['port']) && isset($mainParts['port'])) {
				$parts['port'] = $mainParts['port'];
			}
		}
		if (!isset($parts['path']) && isset($mainParts['path'])) {
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
