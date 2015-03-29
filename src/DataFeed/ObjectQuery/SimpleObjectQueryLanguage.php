<?php

namespace DataFeed\ObjectQuery;


/**
 * Implements a simple query language for object members using the delimiter '->' between members.  Both arrays and objects are supported.
 */
class SimpleObjectQueryLanguage implements ObjectQueryLanguage
{

	/**
	 * @param string $expr  The expression.
	 * @param mixed $obj Either array or object to query.
	 */
	public function query( $expr, $obj )
	{
		$parts = \explode( '->', $expr );

		$cur = $obj;

		foreach ( $parts as $part ) {

			if ( \is_object( $cur ) ) {
				if ( ! isset( $cur->{$part} ) ) {
					throw new ValueNotFoundException( $expr, $obj );
				}
				$cur = $cur->{$part};
			} else if ( \is_array( $cur ) ) {
				if ( ! isset( $cur[$part] ) ) {
					throw new ValueNotFoundException( $expr, $obj );
				}
				$cur = $cur[$part];
			} else {
				throw new ValueNotFoundException( $expr, $obj );
			}

		}

		return $cur;

	}
}