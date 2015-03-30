<?php
/**
 * Database storage for feeds.
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
use DataFeed\FeedHandleFactory;

class DatabaseFeedStore implements FeedStore
{

	const VERSION = '1.0';

	const VERSION_OPTION = 'data-feed-database-version';

	private $wpdb;

	private $feedHandleFactory;

	public function __construct( $wpdb, FeedHandleFactory $feedHandleFactory )
	{
		$this->wpdb = $wpdb;
		$this->feedHandleFactory = $feedHandleFactory;
	}

	private function tableName()
	{
		return $this->wpdb->prefix . 'data_feeds';
	}

	private function fillFeedHandle( $row, FeedHandle $feed )
	{
		$dirty = false;
		if ( $feed->getURL() === null ) {
			$feed->setURL( $row->df_url );
		} else if ( $feed->getURL() != $row->df_url ) {
			$dirty = true;
		}
		if ( $row->df_o_url !== null && $row->df_o_url !== '' ) {
			$feed->setOURL( $row->df_o_url );
		}
		if ( $feed->getInterval() === null ) {
			$feed->setInterval( $row->df_interval );
		} else if ( $feed->getInterval() != $row->df_interval ) {
			$dirty = true;
		}
		if ( $row->df_o_interval !== null ) {
			$feed->setOInterval( $row->df_o_interval );
		}
		$feed->setCreated( $row->df_created );

		return $dirty ? self::LOAD_RESULT_DIRTY : self::LOAD_RESULT_CLEAN;
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
		$result = $this->getNamedRow( $feed->getName() );
		if ( is_array($result) && count($result) === 0 ) {
			return self::LOAD_RESULT_NONEXISTING;
		}

		return $this->fillFeedHandle( $result[0], $feed );
	}

	/**
	 * Store a feed instance on persistent storage.
	 *
	 * @param DataFeed $feed The feed to store.
	 */
	public function storeFeedHandle( FeedHandle $feed )
	{
		$result = $this->getNamedRow( $feed->getName() );
		$data = array();
		$format = array();

		$add = function ( $name, $value, $f ) use ($feed, &$data, &$format) {
			if ( $value !== null ) {
				$data[$name] = $value;
				array_push( $format, $f );
			}
		};

		$add( 'df_url',   $feed->getURL(), '%s' );
		$add( 'df_o_url', $feed->getOURL(), '%s' );
		$add( 'df_interval', $feed->getInterval(), '%d' );
		$add( 'df_o_interval', $feed->getOInterval(), '%d' );

		if ( is_array($result) && count($result) === 0 ) {
			$created = new \DateTime( 'now' );
			$feed->setCreated( $created );

			$add( 'df_created', $feed->getCreated()->format('Y-m-d H:i:s') , '%s' );
			$add( 'df_name', $feed->getName(), '%s' );

			$this->wpdb->insert( $this->tableName(), $data, $format );
		} else {
			$this->wpdb->update( $this->tableName(), $data,
				array(
					'df_name'      => $feed->getName()
				),
				$format, array( '%s' )
			);

		}

	}

	private function getNamedRow( $name )
	{
		$sql = $this->wpdb->prepare( 'SELECT df_name, df_url, df_o_url, df_interval, df_o_interval, df_created FROM ' . $this->tableName() . ' WHERE df_name = %s', $name );

		return $this->wpdb->get_results( $sql );
	}

	/**
	 * Return an array of feeds.
	 *
	 * @param string $search a search string to match.  If {@code null}, match all feeds.
	 * @param string $orderby the field to order the result by.  If {@code null}, the order is unspecified.
	 * @param int $offset the offset to the start of the resulting array in the set of matches.
	 * @param int $limit maximum number of feeds in the resulting array.
	 *
	 * @return an array of FeedHandles.
	 */
	public function searchFeeds( $search = null , $orderby = null , $order = null, $offset = null, $limit = null )
	{
		$sql = 'SELECT * FROM ' . $this->tableName();
		$args = array();

		if ( ! empty($search) ) {
			$sql .= " WHERE df_name LIKE %s OR df_url LIKE %s";
			array_push( $args, '%' . \like_escape($search) . '%' );
		}

		$sql .= " ORDER BY %s %s";
		if ( ! empty($orderby) ) {
			array_push( $args, "df_$orderby" );
		} else {
			array_push( $args, "df_name" );
		}

		if ( ! empty($order) ) {
			array_push( $args, $order === "ASC" ? "ASC" : "DESC" );
		} else {
			array_push( $args, "ASC" );
		}

		if ( ! empty($limit) ) {
			$sql .= " LIMIT %d";
			array_push( $args, $limit );

			if ( ! empty($offset) ) {
				$sql .= " OFFSET %d";
				array_push( $args, $offset );
			}
		}


		$st = $this->wpdb->prepare( $sql, $args );

		$results =  $this->wpdb->get_results( $st );

		$handles = array();

		if (is_array( $results ) ) {
			foreach ( $results as $row ) {
				$feedHandle = $this->feedHandleFactory->create( $row->df_name, null, null );
				$this->fillFeedHandle( $row, $feedHandle );
				array_push( $handles, $feedHandle );
			}
		}

		return $handles;
	}

	public function activate()
	{

		if ( \get_option( self::VERSION_OPTION ) != self::VERSION ) {

			$collate = $this->wpdb->get_charset_collate();

			$name = $this->tableName();

			$sql = "
CREATE TABLE $name (
	df_name char(55) not null primary key,
	df_url varchar(512) not null,
	df_o_url varchar(512) default null,
	df_interval int unsigned not null,
	df_o_interval int unsigned default null,
    df_created datetime not null
) $collate;
"	;

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );

			\add_option( self::VERSION_OPTION, self::VERSION, false, false );
			\update_option( self::VERSION_OPTION, self::VERSION );

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
