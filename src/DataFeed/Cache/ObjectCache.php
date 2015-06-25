<?php

namespace DataFeed\Cache;

class ObjectCache implements Cache
{

	function set( $key, $value, $ttl )
	{
		return \wp_cache_add( $key, $value, 'datafeed', $ttl );
	}

	function get( $key )
	{
		return \wp_cache_get( $key, 'datafeed' );
	}

	function delete( $key )
	{
		return \wp_cache_delete( $key, 'datafeed' );
	}
}