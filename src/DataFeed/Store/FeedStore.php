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

/**
 * Store interface for data feed handles.
 */
interface FeedStore
{

	const LOAD_RESULT_CLEAN = 1;

	const LOAD_RESULT_DIRTY = 2;

	const LOAD_RESULT_NONEXISTING = 3;

	/**
	 * @param DataFeed $feed The feed instance to load data into.  The name of the feed most have been initiated.
	 *
	 * @return LOAD_RESULT_CLEAN       if the configuration of the passed feed matches the existing feed in the store.
	 *         LOAD_RESULT_DIRTY       if the configuration of the passed feed doesn't match the existing feed in the store.
	 *         LOAD_RESULT_NONEXISTING if the feed didn't exist in the store.
	 */
	function loadFeedHandle( FeedHandle $feed );

	/**
	 * Store a feed instance on persistent storage.
	 *
	 * @param DataFeed $feed The feed to store.
	 */
	function storeFeedHandle( FeedHandle $feed );


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
	function searchFeeds( $search, $orderby, $offset, $limit );

	/*
	 * Wordpress actions.
	 */

	/**
	 * Plugin activation.
	 */
	function activate();

	/**
	 * Plugin deactivation.
	 */
	function deactivate();

	/**
	 * Plugin uninstall.
	 */
	function uninstall();

}