<?php

namespace DataFeed\ObjectMerge;

interface ObjectMerge
{

	/**
	 * Merges two objects into one.
	 *
	 * The strategy for the merging is implementation dependent.
	 *
	 * @param mixed $object1 The first object.
	 * @param mixed $object2 The second object.
	 * @param array $ignorefields A set of fields that should be ignored.
	 *
	 * @return mixed The merged object.
	 */
	function merge( $object1, $object2, $ignorefields = array() );

}