<?php

namespace DataFeed\Widget;

use DataFeed\DataFeed;

class DataFeedWidget extends \WP_Widget {

	const NAME = 'data_feed_name';

	const URL = 'data_feed_url';

	const INTERVAL = 'data_feed_interval';

	const PAGINATION_POLICY = 'data_feed_pagination_policy';

	const TIME_FIELD = 'data_feed_time_field';

	const TITLE_FIELD = 'data_feed_title_field';

	const TEXT_FIELD = 'data_feed_text_field';

	const THUMB_FIELD = 'data_feed_thumb_field';

	const LINK_FIELD = 'data_feed_link_field';

	const TYPE = 'data_feed_widget_type';

	const TYPE_TEXT = 'data_feed_type_text';

	const COLUMNS = 'data_feed_columns';

	const EXCERPT_LENGTH = 'data_feed_excerpt_length';

	/**
	 * This configuration is used for overriding the instance
	 * configuration with hard coded values.  By subclassing this
	 * widget and passing a configuration it is possible to make
	 * specialized widgets with fewer configuration options.
	 */
	private $configuration;

	private $ql;

	private $index;

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct(
		$widget_type = 'data_feed_widget',
		$widget_label = null,
		$widget_description = null,
		$configuration = array() )
	{
		if ($widget_label === null) {
			$widget_label = \__('Data Feed Widget', 'data_feed');
		}
		if ($widget_description === null) {
			$widget_description = \__('Display configurable fields of the current item of a data feed', 'data_feed');
		}
		parent::__construct(
			$widget_type,
			$widget_label,
			array( 'description' => $widget_description)
		);


		$this->configuration = $configuration;
		$this->ql = DataFeed::component( DataFeed::OBJECT_QUERY_LANGUAGE );
		$this->index = 0;
	}

	private static function extend_url( $url, $reference )
	{
		$parts = \parse_url( $url );
		if (empty( $parts['scheme'] )) {
			$parts['scheme'] = $reference['scheme'];
		}
		if (empty( $parts['host'] )) {
			$parts['host'] = $reference['host'];
			if (!empty($reference['port'])) {
				$parts['port'] = $reference['port'];
			}
			if (!empty($reference['user'])) {
				$parts['user'] = $reference['user'];
			}
			if (!empty($reference['pass'])) {
				$parts['pass'] = $reference['pass'];
			}
		}
		foreach ( array_keys( $parts ) as $p ) {
			$parts[$p] = \htmlspecialchars( $parts[$p] );
		}
		return DataFeed::build_url( $parts );
	}

	private function query( $field, $item ) {
		$field = preg_replace( '/\$index\b/', $this->index(), $field );
		return $this->ql->query( $field, $item );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		// outputs the content of the widget
		$h = DataFeed::handle(
			$this->c(self::NAME, $instance),
			$this->c(self::URL, $instance),
			$this->c(self::INTERVAL, $instance),
			$this->c(self::PAGINATION_POLICY, $instance) );
		$url = \parse_url( $h->getEffectiveURL() );

		$item = $h->getCurrentItem();


		$columns = $this->c(self::COLUMNS, $instance);
		if ($columns < 1 || $columns > 4) {
			$columns = 1;
		}
		$amount = 3 * $columns;

		$date = '';
		$time_field = $this->c(self::TIME_FIELD, $instance);
		if ( $time_field !== null ) {
			$date_format = \get_option( 'date_format' );
			$date = \date($date_format, \strtotime($this->query( $time_field, $item ) ));
		}

		$text = '';
		$text_field = $this->c(self::TEXT_FIELD, $instance);
		$excerpt_length = $this->c(self::EXCERPT_LENGTH, $instance);
		if ( $text_field !== null ) {
			if (empty($excerpt_length)) {
				$excerpt_length =  \apply_filters( 'excerpt_length', 55 );
			}
			$excerpt_more = \apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
			$text = \wp_trim_words( $this->query($text_field, $item), $excerpt_length, $excerpt_more );
		}

		$title = '';
		$title_field = $this->c(self::TITLE_FIELD, $instance);
		if ( $title_field !== null ) {
			$title = htmlspecialchars($this->query( $title_field, $item ));
		}

		$thumb_url = null;
		$thumb_field = $this->c(self::THUMB_FIELD, $instance);
		if ( $thumb_field !== null ) {
			$thumb = $this->query( $thumb_field, $item );
			$thumb_url = self::extend_url( $thumb, $url );
		}

		$link_url = null;
		$link_field = $this->c(self::LINK_FIELD, $instance);
		if ( $link_field !== null ) {
			$link = $this->query( $link_field, $item );
			$link_url = self::extend_url( $link, $url );
		}

		$type = $this->c(self::TYPE, $instance);
		$type_text = $this->c(self::TYPE_TEXT, $instance);

		$this->increment_index();

		?>
  <div class="col-md-<?php echo $amount; ?> eq">
    <div class="box-wrap dyno <?php if(\is_front_page()) echo 'home'; ?>">
      <a href="<?php echo $link_url; ?>" class="boxlink"></a>
      <div class="header-wrap">
        <h2><?php echo $title; ?></h2>
      </div>
      <div class="infobar <?php echo $type; ?>">
        <time class="<?php echo $type; ?> date" datetime="<?php echo $date; ?>"><?php echo $date; ?></time>
        <span class="type"><span><?php _e($type_text, 'sage'); ?></span></span>
      </div>
      <div class="thumb-wrapper">
         <?php if (!empty($thumb_url)) { ?> <img src="<?php echo $thumb_url; ?>"><?php } ?>
      </div>
      <div class="excerpt">
        <?php echo $text; ?>
      </div>
    </div>
  </div>

		<?php
	}

	private function field( $field, $label, $instance, $template ) {
		if (isset($this->configuration[$field])) {
			return;
		}
		$id = $this->get_field_id( $field );
		$name = $this->get_field_name( $field );
		$label = \esc_html($label);
		$value = \esc_attr($instance[$field]);
		echo '<!-- '  . 'return "' . $template . '";' . '-->';
		echo eval ('return "' . $template . '";');
	}

	private function text_field( $field, $label, $instance ) {
		$this->field( $field, $label, $instance,
		'<p><label for=\"$id\">$label</label><input id=\"$id\" name=\"$name\" type=\"text\" value=\"$value\" class=\"widefat\" style=\"width:100%;\" /></p>' );
	}

	private function option( $key, $value, $selected )
	{
		return '<option ' . ( $selected ? 'selected=\"selected\" ' : '' )  . "value=\\\"$value\\\">$key</option>";
	}

	private function select_field( $field, $label, $instance, $options ) {
		$template = '<label for=\"$id\">$label</label><select id=\"$id\" name=\"$name\" class=\"widefat\" style=\"width:100%;\">';
		foreach ($options as $key => $value) {
			$template .= $this->option( $key, $value, $instance[$field] == $value );
		}
		$template .= '</select></p>';
		$this->field( $field, $label, $instance, $template );
	}

	private function c( $field, $instance ) {
		if (isset($this->configuration[$field])) {
			return $this->configuration[$field];
		}
		if (isset($instance[$field])) {
			return $instance[$field];
		}
		return null;
	}

	private function hasOptions( $options ) {
		foreach ( $options as $field ) {
			if (!isset($this->configuration[$field])) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		// outputs the options form on admin
		$w = (array) $instance;
		$w = array_merge( array(
			self::NAME => '',
			self::URL => '',
			self::INTERVAL => 86400,
			self::PAGINATION_POLICY => 'page-url=next:meta->next&page-update-check=null&limit=10',
			self::TIME_FIELD => 'objects->$index->time',
			self::TITLE_FIELD => 'objects->$index->title',
			self::TEXT_FIELD => 'objects->$index->text',
			self::THUMB_FIELD => 'objects->$index->photo',
			self::LINK_FIELD => 'objects->$index->absolute_url',
			self::TYPE => '',
			self::TYPE_TEXT => '',
			self::COLUMNS => 1,
			self::EXCERPT_LENGTH => '',
		), $w);

		if ($this->hasOptions( array(self::NAME, self::URL, self::INTERVAL, self::PAGINATION_POLICY) )) {
			echo '<h4>' . \esc_html__('Data feed configuration', 'data_feed') . '</h4>';
			$this->text_field( self::NAME, \__('Data feed name:', 'data_feed'), $w);
			$this->text_field( self::URL, \__('Data feed URL:', 'data_feed'), $w);
			$this->text_field( self::INTERVAL, \__('Data feed interval:', 'data_feed'), $w);
			$this->text_field( self::PAGINATION_POLICY, \__('Pagination policy:', 'data_feed'), $w);
		}

		if ($this->hasOptions( array(self::TYPE, self::TYPE_TEXT, self::COLUMNS, self::EXCERPT_LENGTH) )) {
			echo '<h4>'. \esc_html__('Widget display configuration', 'data_feed') . '</h4>';
			$this->select_field( self::COLUMNS, \__('Columns:', 'data_feed'), $w,  array(
				1 => 1,
				2 => 2,
				3 => 3,
				4 => 4,
			));
			$this->text_field( self::EXCERPT_LENGTH, \__('Excerpt length (number of words):', 'data_feed'), $w);
			$this->select_field( self::TYPE, \__('Type:', 'data_feed'), $w, array(
				'News' => 'news',
				'Blog' => 'blog',
				'Video' => 'video',
				'Testimonial' => 'testimonial',
				'Update' => 'update',
				'Map' => 'map',
				'Flow' => 'flow'));
			$this->text_field( self::TYPE_TEXT, \__('Type text:', 'data_feed'), $w);
		}

		if ($this->hasOptions( array(self::TIME_FIELD, self::TITLE_FIELD, self::TEXT_FIELD, self::THUMB_FIELD, self::LINK_FIELD) )) {
			echo '<h4>' . \esc_html__('Data feed field mappings', 'data_feed') . '</h4>';
			echo '<p>A field in an hierarchical item can be referenced by creating a path of field name fragments separated by the delimiter ->.  Object properties and array indicies are referenced by using the same syntax.  Example <b>object->array->1->text</b>.</p>';
			echo '<p>Also the special variable name "$index" can be used in the data field expression, which will be substituted by an index which is incremented for each widget of this type rendered.</p>';
			echo '<p>Exampe: <b>objects->$index->time</b></p>
			$this->text_field( self::TITLE_FIELD, \__('Title field:', 'data_feed'), $w);
			$this->text_field( self::TEXT_FIELD, \__('Text field:', 'data_feed'), $w);
			$this->text_field( self::TIME_FIELD, \__('Time field:', 'data_feed'), $w);
			$this->text_field( self::THUMB_FIELD, \__('Thumb field:', 'data_feed'), $w);
			$this->text_field( self::LINK_FIELD, \__('Link field:', 'data_feed'), $w);
		}
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
		return array_merge($old_instance, $new_instance);
	}

	protected function index() {
		return $this->index;
	}

	protected function increment_index() {
		$this->index++;
	}
}