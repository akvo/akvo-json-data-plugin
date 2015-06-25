<?php

namespace Test;

use DataFeed\ObjectMerge\ObjectMerge;
use DataFeed\ObjectMerge\DefaultObjectMerge;

class TestDefaultObjectMerge extends \PHPUnit_Framework_TestCase
{

	public function test()
	{
		$merger = new DefaultObjectMerge();
		$a = array( "a" => "a" ) ;
		$b = array( "a" => "b" ) ;
		$ao = new \stdClass();
		$ao->a = 'a';
		$bo = new \stdClass();
		$bo->a = 'b';
		$c = array( "b" => "a" );
		$d = array( "b" => "b" );
		$co = new \stdClass();
		$co->b = 'a';
		$do = new \stdClass();
		$do->b = 'b';
		$ac = array( "a" => "a", "b" => "a" );
		$aco = new \stdClass();
		$aco->a = 'a';
		$aco->b = 'a';

		$this->assertNull($merger->merge( null, null ));
		$this->assertNull($merger->merge( null, $a ));
		$this->assertSame( $a, $merger->merge( $a, null ) );
		$this->assertSame( $a, $merger->merge( $a, $a ) );
		$this->assertSame( $a, $merger->merge( $a, $b ) );
		$this->assertSame( $b,  $merger->merge( $b, $a ) );
		$this->assertEquals( $ao, $merger->merge( $ao, $bo ));
		$this->assertEquals( $bo, $merger->merge( $bo, $a ));

		$this->assertSame( $ac, $merger->merge( $a, $c ) );
		$this->assertSame( $ac, $merger->merge( $a, $co ) );
		$this->assertNotSame( $ac, $merger->merge( $c, $a ) );
		$this->assertEquals( $ac, $merger->merge( $c, $a ) );

		$this->assertEquals( $aco, $merger->merge( $ao, $c ) );
		$this->assertEquals( $aco, $merger->merge( $ao, $co ) );
		$this->assertEquals( $aco, $merger->merge( $co, $ao ) );

		$this->assertEquals( $ao, $merger->merge( $co, $ao, array( 'b' ) ) );

		$e = array( 'a' => $a, 'b' => $a );
		$f = array( 'b' => $c, 'c' => $d );

		$ef = array( 'a' => $a, 'b' => $ac, 'c' => $d );

		$this->assertEquals( $ef, $merger->merge( $e, $f ) );

		$abc = array( 'a', 'b', 'c' );
		$def = array( 'd', 'e', 'f' );
		$abcdef = array( 'a', 'b', 'c', 'd', 'e','f' );

		$this->assertSame( $abcdef, $merger->merge( $abc, $def ) );

	}
}