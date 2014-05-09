<?php
/**
 * Orion.
 *
 * @package   Orion_Admin
 * @author    seishynon <sshnn@outlook.com>
 * @license   GPL-2.0+
 * @link      http://sshnn.tumblr.com/
 * @copyright 2014 seishynon
 */

require( plugin_dir_path( __FILE__ ) . 'includes/class-orion-settings-screen.php' );
require( plugin_dir_path( __FILE__ ) . 'includes/class-orion-dashboard-screen.php' );
require( plugin_dir_path( __FILE__ ) . 'includes/class-orion-api.php' );

/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * If you're interested in introducing public-facing
 * functionality, then refer to `class-orion.php`
 *
 * @package Orion_Admin
 * @author seishynon <sshnn@outlook.com>
 */
class Orion_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		$plugin = Orion::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		$this->settings_screen  = new Orion_Settings_Screen();
		$this->dashboard_screen = new Orion_Dashboard_Screen();
		$this->ajax_api         = new Orion_API();

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @TODO:
	 *
	 * - Rename "Orion" to the name your plugin
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {
		$screen = get_current_screen();
		if ( 'toplevel_page_orion_dashboard' == $screen->id ) {
			wp_enqueue_style( $this->plugin_slug . '-forecast-styles', plugins_url( 'assets/css/forecast.css', __FILE__ ), array(), Orion::VERSION );
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), Orion::VERSION );
		}
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @TODO:
	 *
	 * - Rename "Orion" to the name your plugin
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {
		global $is_IE;

		$screen = get_current_screen();
		if ( 'toplevel_page_orion_dashboard' == $screen->id ) {
			if ( $is_IE ) {
				wp_enqueue_script( 'timer_polyfill', plugins_url( 'assets/js/timer_polyfill.js', __FILE__ ), array(), Orion::VERSION );
			}
			wp_enqueue_script( 
				$this->plugin_slug . '-chart-js',
				plugins_url( 'assets/js/chart.min.js', __FILE__ ),
				array(), 
				Orion::VERSION,
				true
			);
			wp_enqueue_script(
				$this->plugin_slug . '-backbone-subviews-js',
				plugins_url( 'assets/js/backbone.subviews.js', __FILE__ ),
				array( 'backbone' ),
				Orion::VERSION,
				true
			);
			wp_enqueue_script(
				$this->plugin_slug . '-admin-script',
				plugins_url( 'assets/js/admin.js', __FILE__ ),
				array(
					'jquery',
					'backbone',
					$this->plugin_slug . '-chart-js',
					$this->plugin_slug . '-backbone-subviews-js'
				),
				Orion::VERSION,
				true
			);
		}
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
			),
			$links
		);
	}
}
