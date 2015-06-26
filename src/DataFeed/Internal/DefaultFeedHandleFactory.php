<?php

namespace DataFeed\Internal;

use DataFeed\FeedHandleFactory;
use DataFeed\Cache\FeedCache;
use DataFeed\Store\FeedStore;
use DataFeed\FeedHandle;
use DataFeed\Pagination\PageUrlFactory;
use DataFeed\Pagination\PageUpdateCheckFactory;

class DefaultFeedHandleFactory implements FeedHandleFactory
{

	private $feed_store;

	private $feed_item_cache;

	private $page_url_factory;

	private $page_update_check_factory;

	public function __construct(
		FeedCache $feed_item_cache,
		PageUrlFactory $page_url_factory,
		PageUpdateCheckFactory $page_update_check_factory )
	{
		$this->feed_item_cache = $feed_item_cache;
		$this->page_url_factory = $page_url_factory;
		$this->page_update_check_factory = $page_update_check_factory;
	}

	public function setFeedStore( FeedStore $feed_store )
	{
		$this->feed_store = $feed_store;
	}

	public function create( $name, $url, $interval, $pagination_policy = null )
	{
		return new FeedHandle(
			$this->feed_store,
			$this->feed_item_cache,
			$this->page_url_factory,
			$this->page_update_check_factory,
			$name,
			$url,
			$interval,
			$pagination_policy
		);
	}
}