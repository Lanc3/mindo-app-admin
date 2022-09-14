<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              www.expansion.ie
 * @since             1.0.0
 * @package           Mindo_App_Admin
 *
 * @wordpress-plugin
 * Plugin Name:       Mindo-app-admin
 * Plugin URI:        Mindo-app-admin
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Aaron Keating
 * Author URI:        www.expansion.ie
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mindo-app-admin
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
add_action('admin_menu', 'test_plugin_setup_menu');

function test_plugin_setup_menu(){
    add_menu_page( 'Mindo Plugin Page', 'Mindo Admin', 'manage_options', 'mindo-plugin', 'mindo_init','admin_page','dashicons-admin-appearance' );
	// array of options
	$data_r = array('push_on_post' => true );
	// add a new option
	add_option('push_on_post_option', $data_r);
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'mindo/v1', '/register_device', array(
	  'method'   => WP_REST_Server::READABLE,
	  'callback' => 'mindo_register_device',
		'args'     => [
			  'expo_push_id' => [
				  'required' => true,
				  'type'     => 'string',
			  ],
		  ],
	) );
  } );
  function mindo_register_device($request){
	  global $wpdb;
	  $expo_push_id = $request->get_param( 'expo_push_id' );
	  
	  if ( ! empty( $expo_push_id ) ) {
		  $table_name = $wpdb->prefix . 'mindo_registered_devices';
		  $now      = new DateTime(); //string value use: %s
		  $datesent = $now->format('Y-m-d H:i:s');
		  
		  if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
      
			  echo "exists";
		}
		  else{
			  echo "does not exist";
		  }
		  $success = $wpdb->insert($table_name, array(
			  'time' => $datesent,
			  'expo_push_id' => $expo_push_id,
		  ));

		  if($success){
			  echo "success";
		  }
		  else{
			  echo 'Failed INSERT to push tavble'.$success;
		  }
	  } else {
		  echo 'No Data Sent';
	  }
	}

add_action( 'rest_api_init', function () {
	register_rest_route( 'mindo/v1', '/deregister_device', array(
	  'method'   => WP_REST_Server::READABLE,
	  'callback' => 'mindo_deregister_device',
		'args'     => [
			  'expo_push_id' => [
				  'required' => true,
				  'type'     => 'string',
			  ],
		  ],
	) );
  } );
  function mindo_deregister_device($request){
	  global $wpdb;
	  $expo_push_id = $request->get_param( 'expo_push_id' );
	  if ( ! empty( $expo_push_id ) ) {
		  $table_name = $wpdb->prefix . 'mindo_registered_devices';
		  $success = $wpdb->query( "DELETE  FROM {$table_name} WHERE expo_push_id = '{$expo_push_id}'" );

		  if($success){
			  echo "success";
		  }
		  else{
			  echo 'Failed DELETE token from table';
		  }
	  } else {
		  echo 'No Data Sent';
	  }
	}

add_action( 'rest_api_init', function () {
	register_rest_route( 'mindo/v1', '/category-posts', array(
	  'method'   => WP_REST_Server::READABLE,
	  'callback' => 'mindo_get_post_by_category',
		'args'     => [
			  'slug' => [
				  'required' => true,
				  'type'     => 'string',
			  ],
			'amount'=> [
				'required' => true,
				'type'=> 'number',
			],
			'paged'=> [
				'required' => true,
				'type'=> 'number',
			],
		  ],
	) );
  } );
function mindo_get_post_by_category($request){
	$slug = $request->get_param( 'slug' );
	$amount = $request->get_param( 'amount' );
	$paged = $request->get_param( 'paged' );
	$resultPost = array();
	if(!empty($slug)){
		$args = array(
        'post_type' => 'post',
        'category_name' => $slug,
        'posts_per_page' => $amount,
	    'post_status' => 'publish',
	    'paged' => $paged,);
		$results = new WP_Query( $args );
		$count = $results->found_posts;
		while ($results->have_posts()){
			$results->the_post();
			$post_id = get_the_ID();
			$content = apply_filters( 'the_content', get_the_content() );
			$excerpt = get_the_excerpt();
			$title = get_the_title();
			$author = get_the_author();
			$date = get_the_date();
			$category_name = get_the_category();
			$attachments = get_posts( array(
				'post_type' => 'attachment',
        		'posts_per_page' => -1,
        		'post_parent' => $post_id,
			));
			$featured_img_url = get_the_post_thumbnail_url($post_id,'full');
			$resultArray = array("ID"=>$post_id,
								 "author"=>$author,
								 "date"=>$date,
								 "content"=>$content,
								 "excerpt"=>$excerpt,
								 "title"=>$title,
								 "categoryName"=>$category_name[0]->cat_name,
								 "media"=>$featured_img_url);
			array_push($resultPost, $resultArray);
		  }
		$resultObj = array("totalPosts"=>$count,
						   "posts"=>$resultPost);
		echo json_encode($resultObj);
	} else 
	{
		  echo 'No Data Sent';
	}
}
add_action( 'rest_api_init', function () {
	register_rest_route( 'mindo/v1', '/author-posts', array(
	  'method'   => WP_REST_Server::READABLE,
	  'callback' => 'mindo_get_post_by_author',
		'args'     => [
			  'author_id' => [
				  'required' => true,
				  'type'     => 'number',
			  ],
			'amount'=> [
				'required' => true,
				'type'=> 'number',
			],
			'paged'=> [
				'required' => true,
				'type'=> 'number',
			],
		  ],
	) );
  } );
function mindo_get_post_by_author($request){
	$author_id = $request->get_param( 'author_id' );
	$amount = $request->get_param( 'amount' );
	$paged = $request->get_param( 'paged' );
	$resultPost = array();
	if(!empty($author_id)){
		$args = array(
        'post_type' => 'post',
        'author__in' => $author_id,
        'posts_per_page' => $amount,
	    'post_status' => 'publish',
	    'paged' => $paged,);
		$results = new WP_Query( $args );
		$count = $results->found_posts;
		while ($results->have_posts()){
			$results->the_post();
			$post_id = get_the_ID();
			$content = apply_filters( 'the_content', get_the_content() );
			$excerpt = get_the_excerpt();
			$title = get_the_title();
			$author = get_the_author();
			$date = get_the_date();
			$category_name = get_the_category();
			$attachments = get_posts( array(
				'post_type' => 'attachment',
        		'posts_per_page' => -1,
        		'post_parent' => $post_id,
			));
			$featured_img_url = get_the_post_thumbnail_url($post_id,'full');
			$resultArray = array("ID"=>$post_id,
								 "author"=>$author,
								 "date"=>$date,
								 "content"=>$content,
								 "excerpt"=>$excerpt,
								 "title"=>$title,
								 "categoryName"=>$category_name[0]->cat_name,
								 "media"=>$featured_img_url);
			array_push($resultPost, $resultArray);
		  }
		$resultObj = array("totalPosts"=>$count,
						   "posts"=>$resultPost);
		echo json_encode($resultObj);
	} else 
	{
		  echo 'No Data Sent';
	}
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'mindo/v1', '/most-read-posts', array(
	  'method'   => WP_REST_Server::READABLE,
	  'callback' => 'mindo_get_most_read_post',
		'args'     => [
			'amount'=> [
				'required' => true,
				'type'=> 'number',
			],
			'paged'=> [
				'required' => true,
				'type'=> 'number',
			],
		  ],
	) );
  } );

function mindo_get_most_read_post($request){
	$amount = $request->get_param( 'amount' );
	$paged = $request->get_param( 'paged' );
	$resultPost = array();
	if(!empty($amount)){
		$args = array(
		'meta_key' => 'post_views_count',
		'orderby' => 'meta_value_num',
        'post_type' => 'post',
        'posts_per_page' => $amount,
	    'post_status' => 'publish',
	    'paged' => $paged,);
		$results = new WP_Query( $args );
		$count = $results->found_posts;
		while ($results->have_posts()){
			$results->the_post();
			$post_id = get_the_ID();
			$content = apply_filters( 'the_content', get_the_content() );
			$excerpt = get_the_excerpt();
			$title = get_the_title();
			$author = get_the_author();
			$date = get_the_date();
			$category_name = get_the_category();
			$attachments = get_posts( array(
				'post_type' => 'attachment',
        		'posts_per_page' => -1,
        		'post_parent' => $post_id,
			));
			$featured_img_url = get_the_post_thumbnail_url($post_id,'full');
			$resultArray = array("ID"=>$post_id,
								 "author"=>$author,
								 "date"=>$date,
								 "content"=>$content,
								 "excerpt"=>$excerpt,
								 "title"=>$title,
								 "categoryName"=>$category_name[0]->cat_name,
								 "media"=>$featured_img_url);
			array_push($resultPost, $resultArray);
		  }
		$resultObj = array("totalPosts"=>$count,
						   "posts"=>$resultPost);
		echo json_encode($resultObj);
	} else 
	{
		  echo 'No Data Sent';
	}
}

add_action( 'rest_api_init', function () {
  register_rest_route( 'mindo/v1', '/search', array(
    'method'   => WP_REST_Server::READABLE,
    'callback' => 'mindo_get_posts_by_search_term',
	  'args'     => [
			'search_term' => [
				'required' => true,
				'type'     => 'string',
			],
		  	'amount'=> [
				'required' => true,
				'type'=> 'number',
			],
			'paged'=> [
				'required' => true,
				'type'=> 'number',
			],
		],
  ) );
} );

function mindo_get_posts_by_search_term($request){
	$search_term = $request->get_param( 'search_term' );
	$amount = $request->get_param( 'amount' );
	$paged = $request->get_param( 'paged' );
	$resultPost = array();
	if(!empty($search_term)){
		$args = array(
        'post_type' => 'post',
        'posts_per_page' => $amount,
	    'post_status' => 'publish',
		's'=> $search_term,
	    'paged' => $paged,);
		$results = new WP_Query( $args );
		$count = $results->found_posts;
		while ($results->have_posts()){
			$results->the_post();
			$post_id = get_the_ID();
			$content = apply_filters( 'the_content', get_the_content() );
			$excerpt = get_the_excerpt();
			$title = get_the_title();
			$author = get_the_author();
			$date = get_the_date();
			$category_name = get_the_category();
			$attachments = get_posts( array(
				'post_type' => 'attachment',
        		'posts_per_page' => -1,
        		'post_parent' => $post_id,
			));
			$featured_img_url = get_the_post_thumbnail_url($post_id,'full');
			$resultArray = array("ID"=>$post_id,
								 "author"=>$author,
								 "date"=>$date,
								 "content"=>$content,
								 "excerpt"=>$excerpt,
								 "title"=>$title,
								 "categoryName"=>$category_name[0]->cat_name,
								 "media"=>$featured_img_url);
			array_push($resultPost, $resultArray);
		  }
		$resultObj = array("totalPosts"=>$count,
						   "posts"=>$resultPost);
		echo json_encode($resultObj);
	} else 
	{
		  echo 'No Data Sent';
	}
}
  function my_awesome_func( $data ) {
	$posts = get_posts( array(
	  'author' => $data['id'],
	) );
	if ( empty( $posts ) ) {
	  return null;
	}
	return $posts[0]->post_title;
  }
function post_published_notification_mindo( $post_id, $post ) {
    $title = $post->post_title;
	$excerpt = $post->post_excerpt;
	send_to_expo_server_trigger($title,$excerpt,$post_id);
}
add_action( 'publish_post', 'post_published_notification_mindo', 10, 2 );

function get_registered_device(){
	global $wpdb;
	$table_name = $wpdb->prefix . 'mindo_registered_devices';
	$result = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $table_name"));
	if($result)
	{
		return $result;
	}
	else
	{
		echo "No data pressent";
	}
	return apply_filters('get_registered_device', $result);
}

function send_to_expo_server_trigger($title,$body,$postID){
	global $wpdb;
	$table_name = $wpdb->prefix . 'mindo_registered_devices';
	$list_of_tokens = get_registered_device();
	$articleData = array("postID"=>$postID);
	$url = "https://exp.host/--/api/v2/push/send";
	foreach ($list_of_tokens as $token) {
		$response = wp_remote_post( $url, array(
	'method' => 'POST',
	'timeout' => 45,
	'redirection' => 5,
	'httpversion' => '1.0',
	'blocking' => true,
	'headers' => array(),
	'body' => array( 'to' => $token->expo_push_id, 'title' => $title , 'body' => $body, 'priority' => 'high','data'=>json_encode($articleData)),
	'cookies' => array()
    )
);
	}
 $obj = json_decode($response,true);
if ( is_wp_error( $response ) ) {
   		$error_message = $response->get_error_message();
   		echo "Something went wrong: $error_message";
	} 
}

  function mindo_init(){
	  require_once( plugin_dir_path( __FILE__ ) .'admin/index.php');

  }
/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'MINDO_APP_ADMIN_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-mindo-app-admin-activator.php
 */
function activate_mindo_app_admin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-mindo-app-admin-activator.php';
	Mindo_App_Admin_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-mindo-app-admin-deactivator.php
 */
function deactivate_mindo_app_admin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-mindo-app-admin-deactivator.php';
	Mindo_App_Admin_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_mindo_app_admin' );
register_deactivation_hook( __FILE__, 'deactivate_mindo_app_admin' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-mindo-app-admin.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_mindo_app_admin() {

	$plugin = new Mindo_App_Admin();
	$plugin->run();

}
run_mindo_app_admin();
