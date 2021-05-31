<?php

/**
 * Fired during plugin activation
 *
 * @link       https://genosha.com.ar
 * @since      1.0.0
 *
 * @package    Posts_Balancer
 * @subpackage Posts_Balancer/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Posts_Balancer
 * @subpackage Posts_Balancer/includes
 * @author     Genosha <hola@genosha.com.ar>
 */
class Posts_Balancer_Activator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate()
	{

		self::create_table_favorites();
		self::create_default_pages();
		self::create_table_session();

		add_action('init', [self::class, 'flush']);
	}
	public static function flush()
	{
		flush_rewrite_rules();
	}
	/**
	 * Create Tables
	 */
	public static function create_tables($table, $sql)
	{
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$table = $wpdb->prefix . $table;

		$sql = 'CREATE TABLE IF NOT EXISTS ' . $table . $sql . $charset_collate;

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	/**
	 * Favorites
	 */
	public static function create_table_favorites()
	{
		$ads_table = 'favorites';

		$sql =  ' ( `ID` INT NOT NULL AUTO_INCREMENT , `user_id` INT(11) NOT NULL , `id_post` INT(11) NOT NULL , PRIMARY KEY (`ID`))';

		self::create_tables($ads_table, $sql);
	}

	public static function create_table_session()
	{
		$ads_table = 'balancer_session';

		$sql =  ' ( `user_id` INT(11) NOT NULL, `content` TEXT NOT NULL, PRIMARY KEY (`user_id`) )';

		self::create_tables($ads_table, $sql);
	}

	public static function page_exists($page_slug)
	{
		global $wpdb;
		$post_title = wp_unslash(sanitize_post_field('post_name', $page_slug, 0, 'db'));

		$query = "SELECT ID FROM $wpdb->posts WHERE 1=1";
		$args  = array();

		if (!empty($page_slug)) {
			$query .= ' AND post_name = %s';
			$args[] = $post_title;
		}

		if (!empty($args)) {
			return (int) $wpdb->get_var($wpdb->prepare($query, $args));
		}

		return 0;
	}

	public static function create_default_pages()
	{
		if (self::page_exists(get_option('personalize', 'personalize')) === 0) {
			$page = self::create_personalize_page();
			update_option('personalize', $page);
		}
	}

	public static function create_personalize_page()
	{
		$args = [
			'post_title' => __('Personalize', 'posts-balancer'),
			'post_status'   => 'publish',
			'post_type'     => 'page',
			'post_content'  => 'This page is for the subscription template, please modify the content in your-theme/balancer/personalize.php',
			'post_author'   => 1,
		];

		$page = wp_insert_post($args);
		return $page;
	}
}
