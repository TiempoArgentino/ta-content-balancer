<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://genosha.com.ar
 * @since             1.0.0
 * @package           Posts_Balancer
 *
 * @wordpress-plugin
 * Plugin Name:       Balancer
 * Plugin URI:        https://genosha.com.ar
 * Description:       Post balancer
 * Version:           1.2
 * Author:            Genosha
 * Author URI:        https://genosha.com.ar
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       posts-balancer
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'POSTS_BALANCER_VERSION', '1.2' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-posts-balancer-activator.php
 */
function activate_posts_balancer() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-posts-balancer-activator.php';
	Posts_Balancer_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-posts-balancer-deactivator.php
 */
function deactivate_posts_balancer() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-posts-balancer-deactivator.php';
	Posts_Balancer_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_posts_balancer' );
register_deactivation_hook( __FILE__, 'deactivate_posts_balancer' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-posts-balancer.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_posts_balancer() {

	$plugin = new Posts_Balancer();
	$plugin->run();

}
run_posts_balancer();
