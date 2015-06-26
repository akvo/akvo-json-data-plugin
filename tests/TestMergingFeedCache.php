<?php

namespace Test;

use DataFeed\Cache\MergingFeedCache;
use DataFeed\Cache\FeedCache;
use DataFeed\Cache\FeedCacheException;
use DataFeed\Pagination\PageUrl;

require_once __DIR__ . '/TestTransientFeedCacheMockFunctions.php';

class TestMergingFeedCache extends \PHPUnit_Framework_TestCase
{

	private function setupInnerCache()
	{
		$innerCache = $this->getMockBuilder('DataFeed\Cache\Cache')->setMethods( array( 'get', 'set', 'delete') )->getMock();

		$transients = array();

		$innerCache->expects( $this->any() )
			->method('get')
			->will( $this->returnCallback( function ( $name ) use (&$transients) {
						if (isset($transients[$name]) ) {
							return $transients[$name];
						}
						return false;
					}));

		$innerCache->expects( $this->any() )
			->method('set')
			->will( $this->returnCallback( function( $key, $value, $interval ) use (&$transients) {
						$transients[$key] = $value;
						return true;
					}));

		return $innerCache;
	}


	public function test()
	{
		$page1 = $this->getMockBuilder('item')->getMock();
		$page2 = $this->getMockBuilder('item')->getMock();
		$page1->value = array( 'value' => array( 'page1' ));
		$page2->value = array( 'value' => array( 'page2' ));

		$next = $this->getMockBuilder('DataFeed\Cache\FeedCache')->setMethods( array('getCurrentItem', 'flush') )->getMock();
		$merger = $this->getMockBuilder('DataFeed\ObjectMerge\ObjectMerge')->setMethods( array( 'merge' ) )->getMock();
		$pageUrl = $this->getMockBuilder('DataFeed\Pagination\PageUrl')->setMethods( array( 'pageUrl' ) )->getMock();
		$pageUpdateCheck = $this->getMockBuilder('DataFeed\Pagination\PageUpdateCheck')->setMethods( array( 'checkUpdates' ) )->getMock();

		$fc = new MergingFeedCache( $next, $this->setupInnerCache(), $merger, $pageUrl, $pageUpdateCheck );

		$next->expects( $this->exactly( 2 ) )
			->method('getCurrentItem')
			->withConsecutive(
				array($this->equalTo( 'foo:0' ), $this->equalTo( 'http://page1' ), $this->anything(), $this->anything() ),
				array($this->equalTo( 'foo:1'), $this->equalTo( 'http://page2' ), $this->anything(), $this->anything() ) )
			->will( $this->onConsecutiveCalls( $page1, $page2 ) );

		$mergedItem = array( 'value' => array( 'page1', 'page2' ) );

		$merger->expects( $this->exactly( 2 ) )
			->method('merge')
			->withConsecutive( 
				array($this->equalTo( array() ), $this->equalTo( $page1 )),
				array($this->equalTo( $page1 ), $this->equalTo( $page2 ))
			)
			->will( $this->onConsecutiveCalls( $page1, $mergedItem ) );

		$pageUrl->expects( $this->exactly( 3 ) )
			->method('pageUrl')
			->will( $this->returnCallback( function ( &$meta, $url, $item, $page ) {
						switch ($page) {
							case 0:
								$meta[PageUrl::PAGE_URL_ARRAY] = array( 'http://page1' );
								return true;
							case 1:
								$meta[PageUrl::PAGE_URL_ARRAY][] = 'http://page2';
								return true;
							case 2:
								return false;
						}
					}) );


		$pageUpdateCheck->expects( $this->once() )
			->method('checkUpdates')
			->will( $this->returnValue( array() ));

		$item = $fc->getCurrentItem('foo', 'http://url', 5);

		$this->assertEquals( $item, $mergedItem );

	}
}