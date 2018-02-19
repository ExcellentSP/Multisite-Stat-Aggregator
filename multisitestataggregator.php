<?php
/**
 * Plugin Name:     Multisite Stat Aggregator
 * Plugin URI:      https://xwp.co/
 * Description:     A frontend widget that shows stats about a site (number of posts, users, etc) and the other sites on a multisite network; the widget is “live”, the stats are updated in the widget once a minute via Ajax communicating over the REST API to custom endpoints for stats.
 * Author:          Shane Phillips
 * Author URI:      shanephillips.tech
 * Text Domain:     multisitestataggregator
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         Multisitestataggregator
 */

define( 'MSSA_SERVER_PATH', plugin_dir_path( __FILE__ ) );
define( 'MSSA_JS_PATH', plugins_url( 'js/', __FILE__ ) );

require_once( MSSA_SERVER_PATH . "classes/MSSA_Widget.php" );

// register MSSA_Widget widget
function mssa_register_widget() {
	register_widget( 'MSSA_Widget' );
}
add_action( 'widgets_init', 'mssa_register_widget' );

/*
 * Register Custom Endpoint
 *
 * URL Example /wp-json/vendor/mssa/v1/site/<id>
 */
add_action('rest_api_init', function(){
	$version = 'v1';
	$namespace = 'vendor/mssa/' . $version;
	$base = 'site';
	register_rest_route( $namespace, '/' . $base . "/(?P<id>\d+)", array(
		array(
			'methods'         => WP_REST_Server::READABLE,
			'callback'        => 'MSSA_Widget::mssa_generate_site_stats',
			'args' => array(
				'id' => array(
					'validate_callback' => function($param) {
						return is_numeric( $param );
					}
				),
			)
		)
	) );
});

