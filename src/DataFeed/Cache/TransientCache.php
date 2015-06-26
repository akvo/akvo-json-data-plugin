<?php

namespace DataFeed\Cache;

class TransientCache implements Cache
{

	function set( $key, $value, $ttl )
	{
		return \set_transient( $key, $value, $ttl );
	}

	function get( $key )
	{
		return \get_transient( $key );
	}

	function delete( $key )
	{
		return \delete_transient( $key );
	}
}