<?php

namespace DataFeed\Admin;

use DataFeed\DataFeed;

class OptionsPage
{

	public static function page()
	{
		$table = new FeedListTable( DataFeed::component( DataFeed::FEED_STORE ) );
		$table->prepare_items();

		?>
		<form action="" method="get" class="search-form">
			 <?php $table->search_box( __( 'Search Data Feeds' ), 'all-data-feeds' ); ?>
    	</form>

		<form id="form-data-feed-list" action='options-general.php?action=all-data-feeds' method='post'>
			 <?php $table->display(); ?>
    	</form>
		<?php

	}

}