<?php

namespace DataFeed\ObjectMerge;

class DefaultObjectMerge implements ObjectMerge
{
	/**
	 * Merges two objects into one according to the following strategy:
	 *
	 * 1. If the first object is an object the result is an object.
	 *
	 * 2. If the first object is an array, the result is an array.
	 *
	 * 3. If the result is an array, any property with numeric key is
	 *    appended to the array.
	 *
	 * 4. If either objects is of any other type than array or object,
	 *    the result is the first object.
	 *    
	 * 5. The result contains all properties that are not ignored from
	 *    both objects.
	 *
	 * 6. The properties that are present in both objects are merged
	 *    recursively.
	 *
	 * @param mixed $object1 The first object.
	 * @param mixed $object2 The second object.
	 * @param array $ignorefields A set of fields that should be ignored.
	 *
	 * @return mixed The merged object.
	 */
	public function merge( $object1, $object2, $ignorefields = array() )
	{

		if ( \is_object( $object2 )) {
			$props2 = array_keys(get_object_vars( $object2 ));
			$get2 = function( $property ) use ( $object2 ) {
				return $object2->{$property};
			};
		} else if ( \is_array( $object2 ) ) {
			$props2 = array_keys( $object2 );
			$get2 = function( $property ) use ( $object2 ) {
				return $object2[$property];
			};
		} else {
			return $object1;
		}
		
		if ( \is_object( $object1 ) ) {
			$props1 = array_keys(get_object_vars( $object1 ));
			$set = function( &$obj, $property, $value ) {
				$obj->{$property} = $value;
			};
			$get1 = function( $property ) use ( $object1 ) {
				return $object1->{$property};
			};
			$result = new \stdClass();
		} else if ( \is_array( $object1 ) ) {
			$props1 = array_keys( $object1 );
			$set = function( &$obj, $property, $value ) {
				if (\is_numeric( $property ) ) {
					$obj[] = $value;
				} else {
					$obj[$property] = $value;
				}
			};
			$get1 = function( $property ) use ( $object1 ) {
				return $object1[$property];
			};
			$result = array();
		} else {
			return $object1;
		}

		return $this->_merge( $result, $props1, $props2, $get1, $get2, $set, $ignorefields );
	}


	private function _merge( &$result, $props1, $props2, $get1, $get2, $set, $ignorefields )
	{
		foreach ( $props1 as $property ) {
			if ( array_search( $property, $ignorefields, true ) !== FALSE ) {
				continue;
			}
			if ( ! (\is_array( $result ) && \is_numeric( $property )) ) {
				$i = array_search( $property, $props2, true );
				if ( $i !== FALSE ) {
					array_splice( $props2, $i, 1 );
					$set($result, $property, $this->merge( $get1( $property ), $get2( $property ), $ignorefields ));
				} else {
					$set($result, $property, $get1( $property ) );
				}
			} else {
				$set($result, $property, $get1( $property ) );
			}
		}

		foreach ( $props2 as $property ) {
			$set($result, $property, $get2( $property ));
		}

		return $result;
	}
}

