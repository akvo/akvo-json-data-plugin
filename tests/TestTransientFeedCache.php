<?php

namespace Test;

use DataFeed\Cache\TransientFeedCache;
use DataFeed\Cache\FeedCache;
use DataFeed\Cache\FeedCacheException;

require_once __DIR__ . '/TestTransientFeedCacheMockFunctions.php';

class TestTransientFeedCache extends \PHPUnit_Framework_TestCase
{

	public function test()
	{
		$item1 = $this->getMockBuilder('item')->getMock();
		$item2 = $this->getMockBuilder('item')->getMock();
		$item1->value = "value1";
		$item2->value = "value2";
		$next = $this->getMockBuilder('DataFeed\Cache\FeedCache')->setMethods( array('getCurrentItem') )->getMock();

		$next->expects( $this->exactly( 2 ) )
			->method('getCurrentItem')
			->will( $this->onConsecutiveCalls( $item1, $item2 ) );

		$cache = new TransientFeedCache( $next );

		$item = $cache->getCurrentItem( 'name', 'http://example.com', 1 );
		$this->assertSame( $item, $item1 );

		$item = $cache->getCurrentItem( 'name', 'http://example.com', 1 );
		$this->assertSame( $item, $item1 );

		\sleep( 2 );

		$item = $cache->getCurrentItem( 'name', 'http://example.com', 1 );
		$this->assertSame( $item, $item2 );

	}

	public function testFailureIsCached()
	{
		$item1 = $this->getMockBuilder('item')->getMock();
		$item1->value = "value1";
		$next = $this->getMockBuilder('DataFeed\Cache\FeedCache')->setMethods( array('getCurrentItem') )->getMock();

		$call = 1;

		$next->expects( $this->exactly(3) )
			->method('getCurrentItem')
			->will( $this->returnCallback( function() use (&$call, $item1) {
						switch ($call) {
							case 1:
								$call++;
								throw new FeedCacheException('next 1');

							case 2:
								$call++;
								throw new FeedCacheException('next 2');
						}
						return $item1;
					} ) );

		$cache = new TransientFeedCache( $next );

		try {
			$item = $cache->getCurrentItem( 'name', 'http://example.com', 1 );
			$this->assertTrue( false , 'Exception was not thrown!');
		} catch (FeedCacheException $e) {
			$this->assertEquals( $e->getMessage(), 'next 1' );
		}

		try {
			$item = $cache->getCurrentItem( 'name', 'http://example.com', 1 );
			$this->assertTrue( false , 'Exception was not thrown!');
		} catch (FeedCacheException $e) {
			$this->assertNotEquals( $e->getMessage(), 'next 1' );
		}

		try {
			$item = $cache->getCurrentItem( 'name', 'http://example.com/', 1 );
			$this->assertTrue( false , 'Exception was not thrown!');
		} catch (FeedCacheException $e) {
			$this->assertEquals( $e->getMessage(), 'next 2' );
		}

		\sleep( 35 );
		$item = $cache->getCurrentItem( 'name', 'http://example.com', 1 );
		$this->assertSame( $item, $item1 );
	}
}