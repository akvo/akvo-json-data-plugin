<?php

namespace Test;

use DataFeed\Pagination\VersionArrayPageUpdateCheck;


class TestVersionArrayPageUpdateCheck extends \PHPUnit_Framework_TestCase
{

	public function test()
	{
		$vauc = new VersionArrayPageUpdateCheck();

		$meta = array();

		$this->assertEquals(
			$vauc->checkUpdates( $meta, array( 'page_versions' => array( 1, 1, 1, 1 ) ) ),
			array( 0, 1, 2, 3 ));

		$this->assertEquals(
			$vauc->checkUpdates( $meta, array( 'page_versions' => array( 1, 1, 1, 1 ) ) ),
			array( ) );


		$this->assertEquals(
			$vauc->checkUpdates( $meta, array( 'page_versions' => array( 1, 2, 1, 1 ) ) ),
			array( 1 ) );


		$this->assertEquals(
			$vauc->checkUpdates( $meta, array( 'page_versions' => array( 1, 2 ) ) ),
			array( 2, 3 ) );


		$this->assertEquals(
			$vauc->checkUpdates( $meta, array( 'page_versions' => array( 1, 2, 2, 2 ) ) ),
			array( 2, 3 ) );
			

		$this->assertEquals(
			$vauc->checkUpdates( $meta, array( 'page_versions' => array( 2, 3, 3, 2 ) ) ),
			array( 0, 1, 2 ) );

		$this->assertEquals(
			$vauc->checkUpdates( $meta, array( 'page_versions' => null ) ),
			array( 0, 1, 2, 3 ) );

		$this->assertEquals(
			$vauc->checkUpdates( $meta, array( 'page_versions' => null ) ),
			array( ) );
			

	}

}