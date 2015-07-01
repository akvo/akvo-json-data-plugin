<?php

namespace DataFeed\ObjectQuery;

class ValueNotFoundException extends \Exception
{
	public function __construct( $expr, $obj )
	{
		parent::__construct( \sprintf(\__("Could not find any value matching the expression '%s' in object:\n"), $expr) .  \print_r($obj, true) );
	}
}