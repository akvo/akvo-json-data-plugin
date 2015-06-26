<?php

namespace Test;

use DataFeed\Store\FeedStore;
use DataFeed\Store\DatabaseFeedStore;
use DataFeed\FeedHandle;
use DataFeed\FeedHandleFactory;

class Result
{
}

require_once __DIR__ . '/TestDatabaseFeedStoreMockFunctions.php';

class TestDatabaseFeedStore extends \PHPUnit_Framework_TestCase
{

	private function newFeedHandle( $store, $name, $url = null, $interval = null )
	{
		$cache = $this->getMockBuilder('DataFeed\Cache\FeedCache')->getMock();
		$pageUrl = $this->getMockBuilder('DataFeed\Pagination\PageUrlFactory')->getMock();
		$pageUpdateCheck = $this->getMockBuilder('DataFeed\Pagination\PageUpdateCheckFactory')->getMock();
		return new FeedHandle( $store, $cache, $pageUrl, $pageUpdateCheck, $name, $url, $interval );
	}

	public function testLoadNonexisting()
	{
		$db = $this->getMockBuilder('Test\wpdb')->setMethods(array('prepare', 'get_results'))->getMock();
		$feedHandleFactory = $this->getMockBuilder('DataFeed\FeedHandleFactory')->getMock();

		$db->prefix = 'prefix';
		$db->result = array();

		$db->expects( $this->once() )
			->method( 'get_results' )
			->will( $this->returnValue( array() ) );

		$store = new DatabaseFeedStore( $db, $feedHandleFactory );

		$feed = $this->newFeedHandle( $store, 'test' );

		$this->assertEquals($store->loadFeedHandle( $feed ), FeedStore::LOAD_RESULT_NONEXISTING );
	}


	public function testStoreNonexisting()
	{
		$db = $this->getMockBuilder('Test\wpdb')->setMethods(array('prepare', 'get_results', 'insert'))->getMock();
		$feedHandleFactory = $this->getMockBuilder('DataFeed\FeedHandleFactory')->getMock();

		$db->prefix = 'prefix';
		$db->result = array();

		$db->expects( $this->once() )
			->method( 'get_results' )
			->will( $this->returnValue( array() ) );

		$db->expects( $this->once() )
			->method( 'insert' );

		$store = new DatabaseFeedStore( $db, $feedHandleFactory );

		$feed = $this->newFeedHandle( $store, 'test' );

		$feed->store();
	}

	public function testStoreExisting()
	{
		$db = $this->getMockBuilder('Test\wpdb')->setMethods(array('prepare', 'get_results', 'update'))->getMock();
		$feedHandleFactory = $this->getMockBuilder('DataFeed\FeedHandleFactory')->getMock();

		$db->prefix = 'prefix';
		$db->result = array();

		$db->expects( $this->once() )
			->method( 'get_results' )
			->will( $this->returnValue( array( 'foo' ) ) );

		$db->expects( $this->once() )
			->method( 'update' );

		$store = new DatabaseFeedStore( $db, $feedHandleFactory );

		$feed = $this->newFeedHandle( $store, 'test' );

		$feed->store();
	}

	public function testLoadExistingDirty()
	{
		$result = $this->getMockBuilder('Result')->getMock();
		$db = $this->getMockBuilder('Test\wpdb')->setMethods(array('prepare', 'get_results' ))->getMock();
		$cache = $this->getMockBuilder('DataFeed\Cache\FeedCache')->getMock();
		$feedHandleFactory = $this->getMockBuilder('DataFeed\FeedHandleFactory')->getMock();

		$db->prefix = 'prefix';
		$result->df_url = 'http://example.com';
		$result->df_o_url = null;
		$result->df_name = 'test';
		$result->df_interval = 42;
		$result->df_o_interval = 44;
		$result->df_key = null;
		$result->df_key_parameter = null;
		$result->df_created =  new \DateTime( 'now' );
		$result->df_pagination_policy = null;
		$result->df_o_pagination_policy = null;

		$db->expects( $this->once() )
			->method( 'get_results' )
			->will( $this->returnValue( array( $result ) ) );

		$store = new DatabaseFeedStore( $db, $feedHandleFactory );

		$feed = $this->newFeedHandle( $store, 'test', 43 );

		$this->assertEquals($store->loadFeedHandle( $feed ), FeedStore::LOAD_RESULT_DIRTY );

	}

	public function testLoadExistingClean()
	{
		$result = $this->getMockBuilder('Result')->getMock();
		$db = $this->getMockBuilder('Test\wpdb')->setMethods(array('prepare', 'get_results' ))->getMock();
		$cache = $this->getMockBuilder('DataFeed\Cache\FeedCache')->getMock();
		$feedHandleFactory = $this->getMockBuilder('DataFeed\FeedHandleFactory')->getMock();

		$db->prefix = 'prefix';
		$result->df_url = 'http://example.com';
		$result->df_o_url = null;
		$result->df_name = 'test';
		$result->df_interval = 42;
		$result->df_o_interval = 44;
		$result->df_key = null;
		$result->df_key_parameter = null;
		$result->df_created =  new \DateTime( 'now' );
		$result->df_pagination_policy = null;
		$result->df_o_pagination_policy = null;

		$db->expects( $this->once() )
			->method( 'get_results' )
			->will( $this->returnValue( array( $result ) ) );

		$store = new DatabaseFeedStore( $db, $feedHandleFactory );

		$feed = $this->newFeedHandle( $store, 'test', 'http://example.com', 42 );

		$this->assertEquals($store->loadFeedHandle( $feed ), FeedStore::LOAD_RESULT_CLEAN );

	}


	public function testSearchFeeds()
	{
		$db = $this->getMockBuilder('Test\wpdb')->setMethods(array('prepare', 'get_results' ))->getMock();
		$feedHandleFactory = $this->getMockBuilder('DataFeed\FeedHandleFactory')->getMock();

		$db->prefix = 'prefix';

		$store = new DatabaseFeedStore( $db, $feedHandleFactory );

		$db->expects( $this->once() )
			->method( 'prepare' )
			->with( $this->equalTo( 'SELECT * FROM prefixdata_feeds WHERE df_name LIKE %s OR df_url LIKE %s ORDER BY %s %s' ),
				$this->equalTo( array( '%like_escape((( s )))%', 'df_name', 'ASC' ) )
			)
			->will( $this->returnValue( 'prepared statement' ) );

		$db->expects( $this->once() )
			->method( 'get_results' )
			->with( $this->equalTo('prepared statement') )
			->will( $this->returnValue( array() ) );

		$store->searchFeeds( 's' );
	}
}