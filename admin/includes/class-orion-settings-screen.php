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

class Orion_Settings_Screen {

	private $plugin_slug = 'orion';

	public function __construct() {
		// Add the options page and menu item.
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
	}

	public function register_settings() {
		register_setting(
			'orion',
			'orion_server_ip'
		);

		register_setting(
			'orion',
			'orion_server_port'
		);

		register_setting(
			'orion',
			'orion_server_username'
		);

		register_setting(
			'orion',
			'orion_server_password'
		);

		add_settings_section(
			'orion_settings_section',
			__( 'JSONAPI Credentials', 'orion' ),
			'',
			'orion'
		);

		add_settings_field(
			'orion_server_ip',
			__( 'Server IP', 'orion' ),
			function() {
				echo '<input type="text" name="orion_server_ip" class="regular-text" value="' . get_option( 'orion_server_ip' ) . '" required>';
			},
			'orion',
			'orion_settings_section'
		);

		add_settings_field(
			'orion_server_port',
			__( 'JSONAPI port', 'orion' ),
			function() {
				echo '<input type="number" name="orion_server_port" value="' . get_option( 'orion_server_port' ) . '" step="1" min="0" max="65536" required>';
			},
			'orion',
			'orion_settings_section'
		);
	
		add_settings_field(
			'orion_server_username',
			__( 'JSONAPI username', 'orion' ),
			function() {
				echo '<input type="text" name="orion_server_username" class="regular-text" value="' . get_option( 'orion_server_username' ) . '" required>';
			},
			'orion',
			'orion_settings_section'
		);

		add_settings_field(
			'orion_server_password',
			__( 'JSONAPI password', 'orion' ),
			function() {
				echo '<input type="password" name="orion_server_password" class="regular-text" value="' . get_option( 'orion_server_password' ) . '" required>';
			},
			'orion',
			'orion_settings_section'
		);
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		// Add a settings page for this plugin to the Settings menu.
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'Orion Settings', $this->plugin_slug ),
			__( 'Orion', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( dirname( dirname( __FILE__ ) ) . '/views/admin-settings.php' );
	}
}