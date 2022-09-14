<?php

/**
 * Fired during plugin deactivation
 *
 * @link       www.expansion.ie
 * @since      1.0.0
 *
 * @package    Mindo_App_Admin
 * @subpackage Mindo_App_Admin/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Mindo_App_Admin
 * @subpackage Mindo_App_Admin/includes
 * @author     Aaron Keating <aaron@expansion.ie>
 */
class Mindo_App_Admin_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'registered_devices';
		$wpdb->query("DELETE FROM $table_name WHERE id > 0");
		delete_option("mindo_backend_version");
	}

}
