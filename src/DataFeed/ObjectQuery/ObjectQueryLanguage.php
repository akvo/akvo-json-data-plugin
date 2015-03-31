<?php

namespace DataFeed\ObjectQuery;

interface ObjectQueryLanguage
{
	/**
	 * @param string $expr An expression in the query language.
	 * @param mixed  $obj  An object to query.
	 * @return mixed The value referenced by the expression.
	 * @throws ValueNotFoundException if the query doesn't match any member of the passed object.
	 */
	public function query( $expr, $obj );
}