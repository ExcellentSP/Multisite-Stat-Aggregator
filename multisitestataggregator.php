<?php
/**
 * Plugin Name:     Multisite Stat Aggregator
 * Plugin URI:      https://xwp.co/
 * Description:     A frontend widget that shows stats about a site (number of posts, users, etc) and the other sites on a multisite network; the widget is “live”, the stats are updated in the widget once a minute via Ajax communicating over the REST API to custom endpoints for stats.
 * Author:          Shane Phillips
 * Author URI:      shanephillips.tech
 * Text Domain:     multisitestataggregator
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Multisitestataggregator
 */

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
			esc_html__( 'MultiSite Satats Aggregator', 'text_domain' ), // Name
			array( 'description' => esc_html__( 'Display a widget with counts of various aspects of different multisites.', 'text_domain' ), ) // Args
		);
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
		$widget .= "<div class='multisite_stats' id='multisite_stats'>" . $this->generate_site_stats( get_current_blog_id() ) . "</div>";
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

	public function generate_site_stats( $site_id ){
		switch_to_blog( $site_id );
		$pages = "<p class='page_count'>Published Pages: " . wp_count_posts('page')->publish . "</p>";
	    $posts = "<p class='post_count'>Published Posts: " . wp_count_posts()->publish . "</p>";
	    $categories = "<p class='term_count'>Categories: " . wp_count_terms( 'category' ) . "</p>";
	    $users = "<p class='user_count'>Users: " . count_users()['total_users'] . "</p>";


	    var_dump(wp_count_terms('category'));
	    return $pages . $posts . $categories . $users;
	}

	/**
	 * Build select element
	 *
	 * @param array $args [[id=>val, title=>val],...]
	 *
	 * @return string html select element
	 */
	private function generate_sites_select( $sites ) {
		$select = "<label for='select-site'>Select a site to see its statistics:</label><select name='select-site' id='select_site'>";
		foreach ( $sites as $site ) {
		    $selected = '';
		    if( get_current_blog_id() === $site['id'] ){
		        $selected = ' selected';
            }
			$select .= "<option value='" . $site['id'] . "'" . $selected . ">" . $site['title'] . "</option>";
		}
		$select .= "</select>";

		return $select;
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

} // class MSS_Foo_Widget

// register Foo_Widget widget
function register_mssa_widget() {
	register_widget( 'MSSA_Widget' );
}
add_action( 'widgets_init', 'register_mssa_widget' );

