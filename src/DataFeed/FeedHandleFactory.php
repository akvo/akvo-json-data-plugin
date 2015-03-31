<?php

namespace DataFeed;

interface FeedHandleFactory
{

	/**
	 * Construct a data feed handle.
	 *
	 * @param string $name     The name of the feed.
	 * @param string $url      The url of the feed.
	 * @param int    $interval The fetch interval in seconds.
	 */
	function create( $name, $url, $interval );

}