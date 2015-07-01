<?php

/**
 * Fetch item from a remote site using the file_get_contents php builtin.
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
class FileGetContentsFeedCache implements FeedCache
{

	/**
	 * @override FeedCache::getCurrentItem
	 */
	public function getCurrentItem($feedName, $url, $interval, $ttl = null )
	{
		$parts = \parse_url( $url );

		if ( $parts === false ) {
			throw new FeedCacheException( 'The url of data feed ' . $feedName . ' cannot be parsed: "' . $url . '"' );
		}

		if ( empty($parts['scheme']) ) {
			$parts['scheme'] = 'http';
		}

		if ( $parts['scheme'] !== 'http' && $parts['scheme'] !== 'https' ) {
			throw new FeedCacheException( 'Unsupported URL scheme for data feed ' . $feedName . ': "' . $parts['scheme'] . '"' );
		}

		$u = $parts['scheme'] . '://' . $parts['host'];

		if ( ! empty( $parts['port'] ) ) {
			$u .= ':' . $parts['port'];
		}

		$u .= $parts['path'];

		if ( ! empty($parts['query']) ) {
			$u .= '?' . $parts['query'];
		}

		if ( ! empty($parts['fragment']) ) {
			$u .= '#' . $parts['fragment'];
		}

		error_log('file get contents on ' . $u );

		$contents = file_get_contents( $u );

		$data = \json_decode( $contents, true );

		if ( $data === null ) {
			throw new FeedCacheException( 'The current data item in data feed ' . $feedName . ' could not be parsed.' );
		}

		return $data;
	}

	public function flush( $feedName )
	{
	}
}