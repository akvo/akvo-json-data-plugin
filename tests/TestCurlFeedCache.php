<?php

namespace Test;

use DataFeed\Cache\CurlFeedCache;

class TestCurlFeedCache extends \PHPUnit_Framework_TestCase
{

	const testUrl = 'http://testfarm.kreablo.se/wikis/api.php?action=query&meta=siteinfo&format=json';
	const testUrlXml = 'http://testfarm.kreablo.se/wikis/api.php?action=query&meta=siteinfo&format=xml';
	const testUrlHtml = 'http://testfarm.kreablo.se/wikis/api.php?action=query&meta=siteinfo&format=html';

	public function testCurlFeedCache()
	{
		$cache = new CurlFeedCache();

		$item = $cache->getCurrentItem( '', self::testUrl, 0 );

		$this->assertTrue( isset($item['query']) );
	}

	public function testCurlFeedCacheXml()
	{
		$cache = new CurlFeedCache();

		$item = $cache->getCurrentItem( '', self::testUrlXml, 0 );

		$this->assertTrue( isset($item->query) );
	}

	/**
	 * @expectedException DataFeed\Cache\FeedCacheException
	 */
	public function testUnsupportedFormat()
	{
		$cache = new CurlFeedCache();

		$item = $cache->getCurrentItem( '', self::testUrlHtml, 0 );
	}

}