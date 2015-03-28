<?php

namespace Test;

use DataFeed\Store\FeedStore;
use DataFeed\Store\DatabaseFeedStore;
use DataFeed\FeedHandle;

class Result
{
}

require_once __DIR__ . '/TestDatabaseFeedStoreMockFunctions.php';

class TestDatabaseFeedStore extends \PHPUnit_Framework_TestCase
{

	public function testLoadNonexisting()
	{
		$db = $this->getMockBuilder('Test\wpdb')->setMethods(array('prepare', 'query'))->getMock();
		$cache = $this->getMockBuilder('DataFeed\Cache\FeedCache')->getMock();

		$db->prefix = 'prefix';
		$db->result = null;

		$db->expects( $this->once() )
			->method( 'query' )
			->will( $this->returnValue( 0 ) );

		$store = new DatabaseFeedStore( $db );

		$feed = new FeedHandle( $store, $cache, 'test' );

		$this->assertEquals($store->loadFeedHandle( $feed ), FeedStore::LOAD_RESULT_NONEXISTING );
	}


	public function testStoreNonexisting()
	{
		$db = $this->getMockBuilder('Test\wpdb')->setMethods(array('prepare', 'query', 'insert'))->getMock();
		$cache = $this->getMockBuilder('DataFeed\Cache\FeedCache')->getMock();

		$db->prefix = 'prefix';
		$db->result = null;

		$db->expects( $this->once() )
			->method( 'query' )
			->will( $this->returnValue( 0 ) );

		$db->expects( $this->once() )
			->method( 'insert' );

		$store = new DatabaseFeedStore( $db );

		$feed = new FeedHandle( $store, $cache, 'test' );

		$feed->store();
	}

	public function testStoreExisting()
	{
		$db = $this->getMockBuilder('Test\wpdb')->setMethods(array('prepare', 'query', 'update'))->getMock();
		$cache = $this->getMockBuilder('DataFeed\Cache\FeedCache')->getMock();

		$db->prefix = 'prefix';
		$db->result = null;

		$db->expects( $this->once() )
			->method( 'query' )
			->will( $this->returnValue( 1 ) );

		$db->expects( $this->once() )
			->method( 'update' );

		$store = new DatabaseFeedStore( $db );

		$feed = new FeedHandle( $store, $cache, 'test' );

		$feed->store();
	}

	public function testLoadExistingDirty()
	{
		$db = $this->getMockBuilder('Test\wpdb')->setMethods(array('prepare', 'query' ))->getMock();
		$cache = $this->getMockBuilder('DataFeed\Cache\FeedCache')->getMock();

		$db->prefix = 'prefix';
		$db->result = new Result();
		$db->result->url = 'http://example.com';
		$db->result->o_url = null;
		$db->result->name = 'test';
		$db->result->interval = 42;
		$db->result->o_interval = 44;
		$db->result->created = new \DateTime( 'now' );

		$db->expects( $this->once() )
			->method( 'query' )
			->will( $this->returnValue( 1 ) );

		$store = new DatabaseFeedStore( $db );

		$feed = new FeedHandle( $store, $cache, 'test' );

		$this->assertEquals($store->loadFeedHandle( $feed ), FeedStore::LOAD_RESULT_DIRTY );

	}

	public function testLoadExistingClean()
	{
		$db = $this->getMockBuilder('Test\wpdb')->setMethods(array('prepare', 'query' ))->getMock();
		$cache = $this->getMockBuilder('DataFeed\Cache\FeedCache')->getMock();

		$db->prefix = 'prefix';
		$db->result = new Result();
		$db->result->url = 'http://example.com';
		$db->result->o_url = null;
		$db->result->name = 'test';
		$db->result->interval = 42;
		$db->result->o_interval = 44;
		$db->result->created = new \DateTime( 'now' );

		$db->expects( $this->once() )
			->method( 'query' )
			->will( $this->returnValue( 1 ) );

		$store = new DatabaseFeedStore( $db );

		$feed = new FeedHandle( $store, $cache, 'test', 'http://example.com', 42 );

		$this->assertEquals($store->loadFeedHandle( $feed ), FeedStore::LOAD_RESULT_CLEAN );

	}


	public function testSearchFeeds()
	{
		$db = $this->getMockBuilder('Test\wpdb')->setMethods(array('prepare', 'query' ))->getMock();

		$db->prefix = 'prefix';

		$store = new DatabaseFeedStore( $db );

		$db->expects( $this->once() )
			->method( 'prepare' )
			->with( $this->equalTo( 'SELECT id, name, url, interval FROM prefixdata-feeds WHERE name LIKE %s OR url LIKE %s' ),
				$this->equalTo( array( '%escape_like((( s )))%' ) )
			)
			->will( $this->returnValue( 'prepared statement' ) );

		$db->expects( $this->once() )
			->method( 'query' )
			->with( $this->equalTo('prepared statement') );

		$store->searchFeeds( 's' );
	}
}