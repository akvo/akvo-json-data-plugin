<?php

namespace DataFeed\Internal;

use DataFeed\FeedHandleFactory;
use DataFeed\Cache\FeedCache;
use DataFeed\Store\FeedStore;
use DataFeed\FeedHandle;

class DefaultFeedHandleFactory implements FeedHandleFactory
{

	private $feed_store;

	private $feed_item_cache;

	public function __construct( FeedCache $feed_item_cache )
	{
		$this->feed_item_cache = $feed_item_cache;
	}

	public function setFeedStore( FeedStore $feed_store )
	{
		$this->feed_store = $feed_store;
	}

	public function create( $name, $url, $interval )
	{
		return new FeedHandle( $this->feed_store, $this->feed_item_cache, $name, $url, $interval );
	}
}