<?php

/**
 * Storage interface for feed handles.
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

namespace DataFeed\Store;

use DataFeed\FeedHandle;

class DatabaseFeedStore implements FeedStore
{

	const VERSION = '1.0';

	const VERSION_OPTION = 'data-feed-database-version';

	private $wpdb;

	public function __construct( $wpdb )
	{
		$this->wpdb = $wpdb;
	}

	private function tableName()
	{
		return $this->wpdb->prefix . 'data-feeds';
	}

	/**
	 * @param DataFeed $feed The feed instance to load data into.  The name of the feed most have been initiated.
	 *
	 * @return LOAD_RESULT_CLEAN       if the configuration of the passed feed matches the existing feed in the store.
	 *         LOAD_RESULT_DIRTY       if the url and the interval of the passed feed doesn't match the existing feed in the store.
	 *         LOAD_RESULT_NONEXISTING if the feed didn't exist in the store.
	 */
	public function loadFeedHandle( FeedHandle $feed )
	{
		list( $n, $result ) = $this->getNamedRow( $feed->getName() );
		if ( $n === 0 ) {
			return self::LOAD_RESULT_NONEXISTING;
		}
		$dirty = false;
		if ( $feed->getURL() != $result->url ) {
			$dirty = true;
			$feed->setURL( $result->url );
		}
		$feed->setOURL( $result->o_url );
		if ( $feed->getInterval() != $result->interval ) {
			$dirty = true;
			$feed->setInterval( $result->interval );
		}
		$feed->setOInterval( $result->o_interval );
		$feed->setCreated( $result->created );

		return $dirty ? self::LOAD_RESULT_DIRTY : self::LOAD_RESULT_CLEAN;
	}

	/**
	 * Store a feed instance on persistent storage.
	 *
	 * @param DataFeed $feed The feed to store.
	 */
	public function storeFeedHandle( FeedHandle $feed )
	{
		list( $n, $result ) = $this->getNamedRow( $feed->getName() );
		if ( $n !== 1 ) {
			$created = new DateTime( 'now' );
			$feed->setCreated( $created );
			$this->wpdb->insert( $this->tableName(), array(
					'name'      => $feed->getName(),
					'url'       => $feed->getURL(),
					'o_url'     => $feed->getOURL(),
					'interval'  => $feed->getInterval(),
					'o_interval' => $feed->getOInterval(),
					'created'    => $feed->getCreated()->format('Y-m-d H:i:s') )
			);
		} else {
			$this->wpdb->update( $this->tableName(), array(
					'url'       => $feed->getURL(),
					'o_url'     => $feed->getOURL(),
					'interval'  => $feed->getInterval(),
					'o_interval' => $feed->getOInterval(),
				),
				array(
					'name'      => $feed->getName()
				)
			);

		}

	}

	private function getNamedRow( $name )
	{
		$sql = $this->wpdb->prepare( 'SELECT id, name, url, o_url, interval, o_interval, created FROM ' . $this->tableName . ' WHERE name = %s', $name );

		$n = $this->wpdb->query( $sql );

		return array( $n, $this->wpdb->result );
	}

	/**
	 * Return an array of feeds.
	 *
	 * @param string $search a search string to match.  If {@code null}, match all feeds.
	 * @param string $orderby the field to order the result by.  If {@code null}, the order is unspecified.
	 * @param int $offset the offset to the start of the resulting array in the set of matches.
	 * @param int $limit maximum number of feeds in the resulting array.
	 *
	 * @return an array of objects containing the following properties:
	 *
	 *    * int    $id       Storage specific identifier.
	 *    * string $name     Feed name.
	 *    * string $url      Feed URL.
	 *    * int    $interval Fetch interval.
	 */
	public function searchFeeds( $search, $orderby, $offset, $limit )
	{
		$sql = 'SELECT id, name, url, interval FROM ' . $this->tableName();
		$args = array();

		if ( $search !== null ) {
			$sql .= " WHERE name LIKE %s OR url LIKE %s";
			array_push( $args, '%' . \escape_like($search) . '%' );
		}

		if ( $orderby !== null ) {
			$sql .= " ORDER BY %s";
			array_push( $args, $orderby );
		}

		if ( $limit !== null ) {
			$sql .= " LIMIT %d";
			array_push( $args, $limit );

			if ( $offset !== null ) {
				$sql .= " OFFSET %d";
				array_push( $args, $offset );
			}
		}

		$st = $this->wpdb->prepare( $sql, $args );

		return $this->wpdb->query( $st );
	}

	public function activate()
	{

		if ( \get_option( self::VERSION_OPTION ) != self::VERSION ) {

			$collate = $this->wpdb->get_charset_collate();

			$name = $this->tableName();

			$sql = "
CREATE TABLE $name {
	id    INT         NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name  TINYTEXT    NOT NULL UNIQUE KEY,
	url   VARCHAR(55) DEFAULT NULL,
	o_url varchar(55) DEFAULT NULL,
	interval INT      UNSIGNED DEFAULT NULL,
	o_interval INT    UNSIGNED DEFAULT NULL,
    created DATETIME  NOT NULL
} $collate;
"	;

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );

			\add_option( self::VERSION_OPTION, $self::VERSION, false, false );
			\update_option( self::VERSION_OPTION, $self::VERSION );

		}
	}

	public function deactivate()
	{
	}

	public function uninstall()
	{
		if ( \get_option( self::VERSION_OPTION ) !== false ) {
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( 'DROP TABLE ' . $this->tableName() . ';');
			\remove_option( SELF::VERSION_OPTION );
		}
	}
}