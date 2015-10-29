<?php

namespace DataFeed\Widget;

class RsrUpdateWidget extends DataFeedWidget {

	private $index = 0;

	public function __construct() {
		parent::__construct(
			'rss_update_widget',
			\__('RSR Update widget'),
			\__('Display an item from the RSR update data feed.'),
			array(
				self::NAME => 'rsr',
				self::URL => 'http://rsr.akvo.org/api/v1/project_update/?format=json',
				self::INTERVAL => 24 * 60 * 60,
				self::PAGINATION_POLICY => 'page-url=next:meta->next&page-update-check=null&limit=10',
				self::TEXT_FIELD => 'objects->$index->text',
				self::TITLE_FIELD => 'objects->$index->title',
				self::TIME_FIELD => 'objects->$index->time',
				self::THUMB_FIELD => 'objects->$index->photo',
				self::LINK_FIELD => 'objects->$index->absolute_url',
				self::TYPE => 'update',
				self::TYPE_TEXT => 'RSR update',
			));
	}

	protected function index() {
		return $this->index;
	}

	protected function increment_index() {
		$this->index++;
	}

}