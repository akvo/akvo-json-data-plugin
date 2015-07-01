<?php

namespace DataFeed\Pagination;
use DataFeed\ObjectQuery\ObjectQueryLanguage;

class PageUrlFactory
{


	private $objectQueryLanguage;

	public function __construct( ObjectQueryLanguage $objectQueryLanguage )
	{
		$this->objectQueryLanguage = $objectQueryLanguage;
	}

	public function create( $arg )
	{
		if ( empty( $arg ) ) {
			return new NextPageUrl( $this->objectQuerlyLanguage );
		}

		$parts = explode( ':', $arg, 2 );

		$name = $parts[0];

		if ( count($parts) == 2 ) {
			$parameter = $parts[1];
		} else {
			$parameter = null;
		}

		switch ($name) {
			case 'null':
				return new NullPageUrl();
			case 'next':
				return new NextPageUrl( $this->objectQueryLanguage, $parameter );
		}

		throw new UnknownPageUrlComponentNameException( $name );
	}

}