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

class Orion_Dashboard_Screen {
	private $plugin_slug;

	public function __construct() {
		$this->plugin_slug = Orion::get_instance()->get_plugin_slug();
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
	}

	public function add_plugin_admin_menu() {
		add_menu_page(
			__( 'Server Dashboard', $this->plugin_slug ),
			__( 'Server Dashboard', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug . '_dashboard',
			array( $this, 'display' )
		);
	}

	public function display() {
		$data = array( 'ajax_url' => admin_url( 'admin-ajax.php' ) );
		wp_localize_script( $this->plugin_slug . '-admin-script', 'orion_data', $data );
		include_once( plugin_dir_path( __FILE__ ) . '../views/dashboard.php' );
	}
}