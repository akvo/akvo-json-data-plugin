<?php

namespace Test;

use DataFeed\Pagination\PageUrl;
use DataFeed\Pagination\NullPageUrl;

class TestNullPageUrl extends \PHPUnit_Framework_TestCase
{

	public function test()
	{

		$pu = new NullPageUrl();

		$meta = array();

		$this->assertTrue( $pu->pageUrl( $meta, 'http://foo', null, 0 ) );

		$this->assertEquals( array( 'http://foo' ), $meta[PageUrl::PAGE_URL_ARRAY] );

		$this->assertFalse( $pu->pageUrl( $meta, 'http://bar', null, 1 ) );

		$this->assertEquals( array( 'http://foo' ), $meta[PageUrl::PAGE_URL_ARRAY] );

		$this->assertFalse( $pu->pageUrl( $meta, 'http://foo', null, 0 ) );

		$this->assertEquals( array( 'http://foo' ), $meta[PageUrl::PAGE_URL_ARRAY] );

		$this->assertTrue( $pu->pageUrl( $meta, 'http://bar', null, 0 ) );

		$this->assertEquals( array( 'http://bar' ), $meta[PageUrl::PAGE_URL_ARRAY] );
	}

}