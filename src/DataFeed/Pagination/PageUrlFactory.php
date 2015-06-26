<?php

namespace DataFeed\Pagination;

class PageUrlFactory
{

	public function create( $arg )
	{
		if ( empty( $arg ) ) {
			return new NextPageUrl();
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
				return new NextPageUrl($parameter);
		}

		throw new UnknownPageUrlComponentNameException( $name );
	}

}