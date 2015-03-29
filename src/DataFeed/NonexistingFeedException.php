<?php

namespace DataFeed;

/**
 * Indicates that an incomplete data feed handle cannot be loaded from persistent storage because it doesn't exist.
 */
class NonexistingFeedException extends \Exception
{
	public function __construct( $feedHandle )
	{
		parent::__construct( "The url of data feed '" . $feedHandle->getName() . "' is missing and the feed doesn't exist in the database." );
	}
}