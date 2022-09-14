<?php

/**
 * Fired during plugin activation
 *
 * @link       www.expansion.ie
 * @since      1.0.0
 *
 * @package    Mindo_App_Admin
 * @subpackage Mindo_App_Admin/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Mindo_App_Admin
 * @subpackage Mindo_App_Admin/includes
 * @author     Aaron Keating <aaron@expansion.ie>
 */
class Mindo_App_Admin_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		global $wpdb;
	  $version = get_option( 'mindo_backend_version', '1.0' );
	  $charset_collate = $wpdb->get_charset_collate();
	  $table_name = 'registered_devices';

	  $sql = "CREATE TABLE $table_name (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	  expo_push_id VARCHAR(255) NOT NULL,
	  UNIQUE KEY id (id)
	  ) $charset_collate;";
	  maybe_create_table( $wpdb->prefix . $table_name, $main_sql_create );
	}

}
