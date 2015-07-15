<?php

namespace Test;

use DataFeed\Store\FeedStore;
use DataFeed\FeedHandle;

class TestFeedHandle extends \PHPUnit_Framework_TestCase
{

	private function getMockStore() {
		$store = $this->getMockBuilder('DataFeed\Store\FeedStore')->getMock();

		return $store;
	}

	public function testOverrides() {
		$store = $this->getMockStore();

		$name = 'handle';
		$url = 'http://example.com/feed';

		$feed = new FeedHandle($this->getMockBuilder('DataFeed\Store\FeedStore')->getMock(),
			$this->getMockBuilder('DataFeed\Cache\FeedCache')->getMock(),
			$this->getMockBuilder('DataFeed\Pagination\PageUrlFactory')->disableOriginalConstructor()->getMock(),
			$this->getMockBuilder('DataFeed\Pagination\PageUpdateCheckFactory')->getMock(),
			$name, $url);


		$this->assertEquals($feed->getInterval(), null);

		$feed->setInterval( 24 * 60 * 60 );

		$this->assertEquals($feed->getName(), $name);
		$this->assertEquals($feed->getURL(), $url);
		$this->assertEquals($feed->getOURL(), null);
		$this->assertEquals($feed->getEffectiveURL(), $url);
		$this->assertEquals($feed->getInterval(), 24 * 60 * 60);
		$this->assertEquals($feed->getOInterval(), null);
		$this->assertEquals($feed->getEffectiveInterval(), 24 * 60 * 60);
		$this->assertEquals($feed->getKey(), null);
		$this->assertEquals($feed->getKeyParameter(), null);


		$oURL = 'https://example.com/feed2';
		$oInterval = 1;

		$feed->setOURL( $oURL );
		$feed->setOInterval( $oInterval );

		$this->assertEquals($feed->getURL(), $url);
		$this->assertEquals($feed->getEffectiveURL(), $oURL);
		$this->assertEquals($feed->getInterval(), 24 * 60 * 60);
		$this->assertEquals($feed->getEffectiveInterval(), $oInterval);

		$feed->setOURL( null );
		$feed->setOInterval( null );

		$this->assertEquals($feed->getName(), $name);
		$this->assertEquals($feed->getURL(), $url);
		$this->assertEquals($feed->getOURL(), null);
		$this->assertEquals($feed->getEffectiveURL(), $url);
		$this->assertEquals($feed->getInterval(), 24 * 60 * 60);
		$this->assertEquals($feed->getOInterval(), null);
		$this->assertEquals($feed->getEffectiveInterval(), 24 * 60 * 60);

		$feed->setKey('12345');

		$this->assertEquals($feed->getEffectiveURL(), $url . '?key=12345');

		$feed->setKeyParameter('foo');

		$this->assertEquals($feed->getEffectiveURL(), $url . '?foo=12345');

		$feed->setOURL( $oURL . '?bar=baz' );

		$this->assertEquals($feed->getEffectiveURL(), $oURL . '?bar=baz&foo=12345');

		$this->assertEquals($feed->asArray(), array(
				'name' => $name,
				'url'  => $url,
				'o_url' => $oURL . '?bar=baz',
				'interval' => 24 * 60 * 60,
				'o_interval' => null,
				'key' => '12345',
				'key_parameter' => 'foo',
				'pagination_policy' => null,
				'o_pagination_policy' => null
			));
	}
}
