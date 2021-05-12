<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://genosha.com.ar
 * @since      1.0.0
 *
 * @package    Posts_Balancer
 * @subpackage Posts_Balancer/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Posts_Balancer
 * @subpackage Posts_Balancer/includes
 * @author     Genosha <hola@genosha.com.ar>
 */
class Posts_Balancer_Deactivator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate()
	{
		self::delete_tables();
		self::delete_pages();
	}

	public static function delete_tables()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'favorites, '. $wpdb->prefix . 'balancer_session';
		$sql = 'DROP TABLE IF EXISTS ' . $table_name;
		$wpdb->query($sql);
	}

	public static function delete_pages()
	{
		if(get_option('personalize')) {
			wp_delete_post(get_option('personalize'));
			delete_option('personalize');
		}
	}
}
