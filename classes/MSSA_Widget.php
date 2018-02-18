<?php
/**
 * Adds MSSA_Widget widget.
 */


class MSSA_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'mssa_widget', // Base ID
			esc_html__( 'MultiSite Stats Aggregator', 'text_domain' ), // Name
			array( 'description' => esc_html__( 'Display a widget with counts of various aspects of different multisites.', 'text_domain' ), ) // Args
		);

		// Register and Enqueue Scripts
		wp_register_script('mssa-ajax-js', MSSA_JS_PATH . 'ajax.js', ['jquery'], '0.1.0', true);
		if( is_active_widget(false, false, $this->id_base, true) ){
			wp_enqueue_script( 'mssa-ajax-js' );
		}
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		$widget = $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			$widget .= $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}
		$sites_info = $this->get_sites_select_info();
		if ( ! empty( $sites_info ) ) {
			$widget .= $this->generate_sites_select( $sites_info );
		}
		$stats = json_decode( self::mssa_generate_site_stats( get_current_blog_id() ), true );
		$widget .= "<div class='mssa_multisite_stats' id='mssa_multisite_stats'>" . $stats['body'] . "</div>";
		$widget .= $args['after_widget'];
		echo $widget;
	}

	/**
	 * Get network sites' id and name from WordPress
	 *
	 * @link https://developer.wordpress.org/reference/functions/get_sites/
	 * @link https://codex.wordpress.org/WPMU_Functions/get_blog_details
	 *
	 * @return array [[id=>val, title=>val],...]
	 */
	private function get_sites_select_info() {
		$sites_info = [];
		$sites      = get_sites();
		if ( ! empty( $sites ) ) {
			foreach ( $sites as $site_object ) {
				$site_details['id']    = (int) $site_object->blog_id;
				$site_details['title'] = ( get_blog_details( $site_details['id'] ) )->blogname;
				array_push( $sites_info, $site_details );
			}
		}

		return $sites_info;
	}

	/**
	 * Build select element
	 *
	 * @param array $sites [[id=>val, title=>val],...]
	 *
	 * @return string html select element
	 */
	private function generate_sites_select( $sites ) {
		$select = "<label for='mssa_select_site'>Select a site to see its statistics:</label><select name='mssa_select_site' id='mssa_select_site'>";
		foreach ( $sites as $site ) {
			$selected = '';
			if ( get_current_blog_id() === $site['id'] ) {
				$selected = ' selected';
			}
			$select .= "<option value='" . $site['id'] . "'" . $selected . ">" . $site['title'] . "</option>";
		}
		$select .= "</select>";

		return $select;
	}

	/**
	 * @param $site int or WP_REST_Response Object
	 *
	 * @return string
	 */
	public static function mssa_generate_site_stats( $site ){
		if( is_numeric( $site ) ){
			switch_to_blog( $site );
		}elseif( is_object( $site ) ){
			switch_to_blog( $site->get_url_params()['id'] );
		}
		$pages = "<p class='page_count'>Published Pages: " . wp_count_posts('page')->publish . "</p>";
		$posts = "<p class='post_count'>Published Posts: " . wp_count_posts()->publish . "</p>";
		$categories = "<p class='term_count'>Categories: " . wp_count_terms( 'category' ) . "</p>";
		$users = "<p class='user_count'>Users: " . count_users()['total_users'] . "</p>";
		restore_current_blog();

		return json_encode([
			'body' => $pages . $posts . $categories . $users
		]);
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Multisite Stats', 'text_domain' );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'text_domain' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

} // class MSS_Widget