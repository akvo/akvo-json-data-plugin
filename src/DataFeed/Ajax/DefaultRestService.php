<?php

namespace DataFeed\Ajax;

use DataFeed\Store\FeedStore;
use DataFeed\Cache\FeedCache;
use DataFeed\NonexistingFeedException;
use DataFeed\FeedHandleFactory;

class DefaultRestService implements RestService
{

	private $dataFetcher;

	private $factory;

	private $cache;

	private $data = null;

	public function __construct(FeedHandleFactory $factory, FeedCache $cache, RequestDataFetcher $dataFetcher)
	{
		$this->dataFetcher = $dataFetcher;
		$this->factory = $factory;
		$this->cache = $cache;
	}

	private function getData()
	{
		if ($this->data === null) {
			$this->data = $this->dataFetcher->fetch();
		}
		return $this->data;
	}


	public function handle()
	{
		if (!isset($_GET['item_name'])) {
			$this->error(400, 'The item_name query parameter is required');
		}

		$item = $_GET['item_name'];

		$method = $_SERVER['REQUEST_METHOD'];

		try {
			switch ($method) {
				case 'PUT':
					$this->put( $item );
					break;
				case 'PATCH':
					$this->patch( $item );
					break;
				case 'DELETE':
					$this->delete( $item );
					break;
				case 'GET':
					$this->get( $item );
					break;
				default:
					$this->error(405, sprintf(__('Unsupported method: %s', 'data-feed'), $method ));
			}
		} catch (\Exception $e) {
			$msg = "" . $e;
			str_replace( "\n", '<br />', $msg );
			$this->error(500, sprintf(__('Caught exception %s', 'data-feed'), $msg));
		}
		exit(0);
	}

	private function sendHeaders()
	{
		\header('Content-Type: application/json');
	}

	private function sendData( $data )
	{
		echo \json_encode( $data );
	}

	private function error( $code, $msg )
	{
		if (function_exists('\http_response_code')) {
			\http_response_code( $code );
		} else {
			header('HTTP/1.1 ' . $code . ' ' . $msg);
		}
		$this->sendHeaders();
		$this->sendData( array( 'message' => $msg ) );
		exit(0);
	}

	private function put( $item )
	{
		$data = $this->getData();

		if (!isset($data['url'])) {
			$this->error(400, 'The parameter url is mandatory!');
		}

		if (isset($data['interval'])) {
			$interval = $data['interval'];
		} else {
			$interval = null;
		}

		$feed = $this->factory->create( $item, $data['url'], $interval );
		$feed->load();

		$this->merge( $feed, $data );

		$feed->store();

		$this->sendHeaders();
		$this->sendData( $feed->asArray() );
	}

	private function patch( $item )
	{
		try {
			$feed = $this->factory->create( $item, null, null );
			$feed->load();

			$this->merge( $feed, $this->getData() );
			$feed->store();

			$this->sendHeaders();
			$this->sendData( $feed->asArray() );
		} catch (NonexistingFeedException $e) {
			$this->error(404, 'The feed handle ' . $item  . ' does not exist!');
		}
	}

	private function delete( $item )
	{
		try {
			$feed = $this->factory->create( $item, null, null );
			$feed->load();
			$feed->remove();

			$this->sendHeaders();
			$this->sendData( null );
		} catch (NonexistingFeedException $e) {
			$this->error(404, 'The feed handle ' . $item  . ' does not exist!');
		}
	}

	private function get( $item )
	{
		try {
			$feed = $this->factory->create( $item, null, null );
			$feed->load();

			$this->sendHeaders();
			$this->sendData( $feed->asArray() );

		} catch (NonexistingFeedException $e) {
			$this->error(404, 'No feed handle for ' . $item . ' exists!');
		}
	}


	private function merge( $feed, $data ) {
		if ( isset($data['name']) && $data['name'] != $feed->getName() ) {
			throw new Exception('Name mismatch: ' . $feed->getName() . ' != ' . $data['name']);
		}

		if ( isset($data['url']) ) {
			$feed->setUrl( $data['url'] );
		}

		if ( \array_key_exists('o_url', $data) ) {
			$feed->setOUrl( $data['o_url'] );
		}

		if ( isset($data['interval']) ) {
			$feed->setInterval( $data['interval'] );
		}

		if ( \array_key_exists('o_interval', $data) ) {
			$feed->setOInterval( $data['o_interval'] );
		}

		if ( \array_key_exists('key', $data) ) {
			$feed->setKey( $data['key'] );
		}

		if ( \array_key_exists('key_parameter', $data)) {
			$feed->setKeyParameter( $data['key_parameter'] );
		}

		if ( \array_key_exists('o_pagination_policy', $data)) {
			$feed->setOPaginationPolicy( $data['o_pagination_policy'] );
		}

		if ( \array_key_exists('pagination_policy', $data)) {
			$feed->setPaginationPolicy( $data['pagination_policy'] );
		}
	}
}