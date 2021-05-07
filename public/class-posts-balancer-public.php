<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://genosha.com.ar
 * @since      1.0.0
 *
 * @package    Posts_Balancer
 * @subpackage Posts_Balancer/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Posts_Balancer
 * @subpackage Posts_Balancer/public
 * @author     Genosha <hola@genosha.com.ar>
 */
class Posts_Balancer_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->front_classes();

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Posts_Balancer_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Posts_Balancer_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/posts-balancer-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Posts_Balancer_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Posts_Balancer_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/posts-balancer-public.js', array( 'jquery' ), $this->version, false );

	}

	public function front_classes()
	{
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-posts-balancer-template.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-posts-balancer-user.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-posts-balancer-personalize.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-posts-balancer-front.php';
	}

}
