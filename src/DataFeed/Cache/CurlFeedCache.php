<?php

/**
 * Fetch item from a remote site using curl.
 *
 * Not really a cache, unless the url points to a caching proxy.  This is more the backend of feed, made available through the cache interface.
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
 * @since      File available since Release 1.0
 */

namespace DataFeed\Cache;

/**
 * Curl implementation of feed cache interface.
 */
class CurlFeedCache implements FeedCache
{

	/**
	 * @override FeedCache::getCurrentItem
	 */
	public function getCurrentItem($feedName, $url, $interval)
	{
		$ch = \curl_init( $url );
		try {
			\curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			\curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type' => 'application/json; text/xml') );

			$t = curl_exec( $ch );

			if ($t === false) {
				throw new FeedCacheException("Could not fetch feed item in feed '$feedName': " . curl_error($ch));
			}

			$contentType = curl_getinfo( $ch, CURLINFO_CONTENT_TYPE );

			if ( $contentType === null ) {
				$contentType = 'application/json';
			}

			$parts = \explode( ';', $contentType );
			$contentType = $parts[0];

			switch ( $contentType ) {
				case 'application/json':
					$result = \json_decode( $t, true );
					if ( $result === null ) {
						throw new FeedCacheException( "Could not parse json i feed '$feedName'." );
					}
					return $result;

				case 'text/xml':
					$result = \simplexml_load_string( $t );
					if ( $result === false ) {
						throw new FeedCacheException( "Could not parse xml in feed '$feedName'." );
					}
					return \json_decode( \json_encode( $result ) );

				default:
					throw new FeedCacheException( "Unsupported content type in feed '$feedName': '$contentType'" );
			}
		} catch (\Exception $e) {
			\curl_close( $ch );
			throw $e;
		}
	}
}
