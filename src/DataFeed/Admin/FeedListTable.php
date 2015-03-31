<?php
/**
 * Table for managing data feeds.
 *
 * @package WordPress
 * @subpackage DataFeed
 * @since 1.0
 */

namespace DataFeed\Admin;

use DataFeed\Store\FeedStore;

/**
 * Table for presenting the feeds in the administration settings.
 */
class FeedListTable extends \WP_List_Table {

	private $feedStore;

	function __construct( FeedStore $feedStore, $args = array() ) {

		$this->feedStore = $feedStore;

		parent::__construct( array(
			'plural' => 'datafeeds',
			'singular' => 'datafeed',
		) );
	}

	function ajax_user_can() {
		return current_user_can( 'manage_options' );
	}

	function prepare_items() {
		global $cat_id, $s, $orderby, $order;

		wp_reset_vars( array( 'action', 'feed_id', 'orderby', 'order', 's' ) );

		$limit = $this->get_items_per_page( 'data_feed_admin_items_per_page' );
		$page = $this->get_pagenum();

		$this->items = $this->feedStore->searchFeeds( $s, $orderby, ($page - 1) * $limit, $limit );
	}

	function no_items() {
		_e( 'There are currently no data feeds.', 'data-feed' );
	}

	function get_bulk_actions() {
		$actions = array();
		$actions['delete'] = __( 'Delete' );

		return $actions;
	}

	function get_columns() {
		return array(
			'cb'         => '<input type="checkbox" />',
			'name'       => _x( 'Name', 'feed name' ),
			'url'        => __( 'URL' ),
			'interval'   => _x( 'Interval', 'Data feed fetch interval' )
		);
	}

	function get_sortable_columns() {
		return array(
			'name'     => 'name',
			'url'      => 'url',
			'interval' => 'interval',
		);
	}

	function display_rows() {

		$alt = 0;

		foreach ( $this->items as $feed ) {
			$feed = self::sanitize( $feed );

			$short_url = url_shorten( $feed->getUrl() );

			$style = ( $alt++ % 2 ) ? '' : ' class="alternate"';

			$edit_link = self::get_edit_feed_link( $feed->getName() );
?>
		<tr id="link-<?php echo $feed->getName(); ?>" <?php echo $style; ?>>
<?php

			 foreach ( $this->get_columns() as $column_name => $column_display_name ) {
				$class = "class='column-$column_name'";

				$style = '';

				$attributes = $class . $style;

				switch ( $column_name ) {
					case 'cb': ?>
						<th scope="row" class="check-column">
						<label class="screen-reader-text" for="cb-select-<?php echo $feed->getName(); ?>"><?php echo sprintf( __( 'Select %s' ), $feed->getName() ); ?></label>
							<input type="checkbox" name="feedcheck[]" id="cb-select-<?php echo esc_attr( $feed->getName() ); ?>" value="<?php echo esc_attr( $feed->getName() ); ?>" />
						</th>
						<?php
						break;

					case 'name':
						echo "<td $attributes><strong><a class='row-title' href='$edit_link' title='" . esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;' ), $feed->getName() ) ) . "'>{$feed->getName()}</a></strong><br />";

						$actions = array();
						$actions['edit'] = '<a href="' . $edit_link . '">' . __( 'Edit' ) . '</a>';
						$actions['delete'] = "<a class='submitdelete' href='" . wp_nonce_url( "link.php?action=delete&amp;feed_id={$feed->getName()}", 'delete-bookmark_' . $feed->getName() ) . "' onclick=\"if ( confirm( '" . esc_js( sprintf( __( "You are about to delete this feed '%s'\n  'Cancel' to stop, 'OK' to delete." ), $feed->getName() ) ) . "' ) ) { return true;}return false;\">" . __( 'Delete' ) . "</a>";
						echo $this->row_actions( $actions );

						echo '</td>';
						break;

					case 'url':
						echo "<td $attributes>$short_url</td>";
						break;

					case 'interval':
						echo "<td $attributes>{$feed->getInterval()}</td>";
						break;

					defalt:
						echo "<td $attributes>Unknown column</td>";
				}
			}
?>
		</tr>
<?php
		}
	}

	private static function get_edit_feed_link( $feed )
	{
		// TODO
	}

	private static function sanitize( $feed )
	{
		// TODO
		return $feed;
	}
}
