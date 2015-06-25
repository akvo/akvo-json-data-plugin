<?php

namespace Test;

use DataFeed\Cache\MergingFeedCache;
use DataFeed\Cache\FeedCache;
use DataFeed\Cache\FeedCacheException;

require_once __DIR__ . '/TestTransientFeedCacheMockFunctions.php';

class TestMergingFeedCache extends \PHPUnit_Framework_TestCase
{

	public function test()
	{
		$item1 = $this->getMockBuilder('item')->getMock();
		$item2 = $this->getMockBuilder('item')->getMock();
		$item1->value = array("value1");
		$item1->next = 'foo';
		$item2->value = array("value2");

		$next = $this->getMockBuilder('DataFeed\Cache\FeedCache')->setMethods( array('getCurrentItem', 'flush') )->getMock();
		$merger = $this->getMockBuilder('DataFeed\ObjectMerge\ObjectMerge')->setMethods( array( 'merge' ) )->getMock();

		$fc = new MergingFeedCache( $next, $merger );

		$next->expects( $this->exactly( 2 ) )
			->method('getCurrentItem')
			->will( $this->onConsecutiveCalls( $item1, $item2 ) );

		$merger->expects( $this->exactly( 1 ) )
			->method('merge')
			->with( $this->equalTo( $item1 ), $this->equalTo( $item2 ) )
			->will( $this->returnValue( null ) );

		$item = $fc->getCurrentItem('foo', 'http://url', 5);

	}
}