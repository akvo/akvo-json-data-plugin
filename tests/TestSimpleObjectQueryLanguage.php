<?php

namespace Test;

use DataFeed\ObjectQuery\SimpleObjectQueryLanguage;

class Test
{
}

class TestSimpleObjectQueryLanguage extends \PHPUnit_Framework_TestCase
{
	public function testObjectQuery()
	{
		$ql = new SimpleObjectQueryLanguage();

		$test = new Test();

		$test->{' * '} = array( 'zero', '$' => '""',  ' ' => ' ', '' => 'empty', 'sub' => new Test() );
		$test->{' * '}['sub']->bar = 'bar';

		$this->assertArrayHasKey( 'sub', $ql->query(' * ', $test ) );
		$this->assertEquals( $ql->query(' * ->0', $test), 'zero');
		$this->assertEquals( $ql->query(' * ->$', $test), '""');
		$this->assertEquals( $ql->query(' * -> ', $test), ' ');
		$this->assertEquals( $ql->query(' * ->', $test), 'empty');
		$this->assertEquals( $ql->query(' * ->sub->bar', $test), 'bar');
	}
}