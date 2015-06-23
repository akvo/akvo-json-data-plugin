<?php

namespace DataFeed\Ajax;

class DefaultRequestDataFetcher implements RequestDataFetcher
{

	public function fetch()
	{
		$data = \json_decode(\file_get_contents('php://input'), true );

		return $data;
	}

}