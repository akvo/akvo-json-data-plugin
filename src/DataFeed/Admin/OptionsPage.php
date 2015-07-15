<?php

namespace DataFeed\Admin;

use DataFeed\DataFeed;

class OptionsPage
{

	private static function store()
	{
		return DataFeed::component( DataFeed::FEED_STORE );
	}

	public static function page()
	{
		?>
		<h2>Data feed handle attribute override</h2>
		<div id="datafeed-admin-options-add"><a id="datafeed-admin-options-add-link" href="#"><?php _e('Add datafeed', 'data-feed'); ?></a></div>
		<div class="datafeed-admin-options">
		<div class="datafeed-admin-option-feeds" id="datafeed-admin-option-feeds">
		</div>
		</div>
		<?php

		self::bootstrapScript();
		self::addFeedDialog();
	}

	private static function addFeedDialog()
	{
		?>
		<div style="display:none;" id="datafeed-add-feed-dialog">
		<form id="datafeed-add-feed-dialog-form" action="#">
		<div><span><label for="name">Name: <input type="text" name="name"></label></span></div>
		<div><span><label for="url">URL: <input type="text" name="url"></label></span></div>
		<div><span><label for="interval">Interval: <input type="text" name="interval"></label></span></div>
		<div><input type="submit" value="<?php echo __('Add', 'data-feed'); ?>" /></div>
		<div id="datafeed-add-feed-error-msg" style="color:red"></div>
		</form>
		</div>
		<?php
	}

	private static function bootstrapScript()
	{
		$feeds = array();
		foreach( self::store()->searchFeeds() as $feed ) {
			$feeds[] = $feed->asArray();
		}
		?>
		<script>
					(function($) {
							var feeds;
							function addFeed(feed) {
									feeds.add(feed, {merge:true});
							}
							function addView(feed) {
									var view = new wp.datafeed.DataFeedView({model: feed});
									view.render();
									$('#datafeed-admin-option-feeds').append( view.$el );
							}
							$(document).on('datafeed:model-loaded', function() {
									feeds = new window.wp.datafeed.DataFeedCollection();
									feeds.on('add', addView);
									feeds.on('reset', function (col) {
											col.each(addView);
									});
									feeds.reset(<?php echo json_encode($feeds); ?>);
							});
							$('#datafeed-admin-options-add-link').on('datafeed:added', function(event, feed) {
									addFeed(feed);
							});
					})(jQuery);
		</script>
		<?php

	}

}