<?php

namespace DataFeed\Pagination;

class PageUpdateCheckFactory
{

	public function create( $arg )
	{

		if (empty( $arg ) ) {
			return new VersionArrayPageUpdateCheck();
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
				return new NullPageUpdateCheck();
			case 'version-array':
				return new VersionArrayPageUpdateCheck( $parameter );
		}

		throw new UnknownPageUpdateCheckComponentNameException( $name );
	}


}