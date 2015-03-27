<?php
/**
 * Table for managing data feeds.
 *
 * @package WordPress
 * @subpackage DataFeed
 * @since 1.0
 */

namespace DataFeed\Plugin;

/**
 * Table for presenting the feeds in the administration settings.
 */
class FeedListTable extends WP_List_Table {

	function __construct( $args = array() ) {
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

		$this->items = array();
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
			$feed->name = esc_attr( $feed->name );

			$short_url = url_shorten( $feed->url );

			$style = ( $alt++ % 2 ) ? '' : ' class="alternate"';

			$edit_link = self::get_edit_feed_link( $link );
?>
		<tr id="link-<?php echo $feed->id; ?>" <?php echo $style; ?>>
<?php

			list( $columns, $hidden ) = $this->get_column_info();

			foreach ( $columns as $column_name => $column_display_name ) {
				$class = "class='column-$column_name'";

				$style = '';
				if ( in_array( $column_name, $hidden ) )
					$style = ' style="display:none;"';

				$attributes = $class . $style;

				switch ( $column_name ) {
					case 'cb': ?>
						<th scope="row" class="check-column">
							<label class="screen-reader-text" for="cb-select-<?php echo $feed->id; ?>"><?php echo sprintf( __( 'Select %s' ), $feed->name ); ?></label>
							<input type="checkbox" name="feedcheck[]" id="cb-select-<?php echo esc_attr( $feed->id ); ?>" value="<?php echo esc_attr( $feed->id ); ?>" />
						</th>
						<?php
						break;

					case 'name':
						echo "<td $attributes><strong><a class='row-title' href='$edit_link' title='" . esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;' ), $feed->name ) ) . "'>$feed->name</a></strong><br />";

						$actions = array();
						$actions['edit'] = '<a href="' . $edit_link . '">' . __( 'Edit' ) . '</a>';
						$actions['delete'] = "<a class='submitdelete' href='" . wp_nonce_url( "link.php?action=delete&amp;feed_id=$feed->id", 'delete-bookmark_' . $feed->id ) . "' onclick=\"if ( confirm( '" . esc_js( sprintf( __( "You are about to delete this feed '%s'\n  'Cancel' to stop, 'OK' to delete." ), $feed->name ) ) . "' ) ) { return true;}return false;\">" . __( 'Delete' ) . "</a>";
						echo $this->row_actions( $actions );

						echo '</td>';
						break;
					case 'url':
						echo "<td $attributes>$short_url</td>";
						break;
					case 'interval':
						echo "<td $attributes>{$feed->interval}</td>";
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
