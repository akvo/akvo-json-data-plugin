<?php

namespace DataFeed\Cache;

class FeedCacheException extends \Exception
{

	public function __construct( $msg )
	{
		parent::__construct( $msg );
	}

}