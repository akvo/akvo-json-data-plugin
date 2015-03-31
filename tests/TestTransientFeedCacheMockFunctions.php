<?php

$transients = array();

function set_transient( $name, $value )
{
	global $transients;

	$transients[$name] = $value;
}


function get_transient( $name )
{
	global $transients;

	if (isset($transients[$name]) ) {
		return $transients[$name];
	}
	return false;
}