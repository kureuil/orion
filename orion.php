<?php
/**
 * @package   Orion
 * @author    seishynon <sshnn@outlook.com>
 * @license   GPL-2.0+
 * @link      http://sshnn.tumblr.com/
 * @copyright 2014 seishynon
 *
 * @wordpress-plugin
 * Plugin Name:       Orion
 * Plugin URI:        @TODO
 * Description:       Administrate your Minecraft server from your WordPress dashboard
 * Version:           1.0.0
 * Author:            seishynon
 * Author URI:        sshnn@outlook.com
 * Text Domain:       orion-locale
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/<owner>/<repo>
 * WordPress-Plugin-Boilerplate: v2.6.1
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require( plugin_dir_path( __FILE__ ) . 'includes/JSONAPI.php' );

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'public/class-orion.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
register_activation_hook( __FILE__, array( 'Orion', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Orion', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'Orion', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

if ( is_admin() ) {
	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-orion-admin.php' );
	add_action( 'plugins_loaded', array( 'Orion_Admin', 'get_instance' ) );
}
