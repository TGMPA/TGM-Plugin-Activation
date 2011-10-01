<?php
/**
 * This file represents an example of the code that themes would use to register
 * the required plugins.
 *
 * It is expected that theme authors would copy and paste this code into their
 * functions.php file, and amend to suit.
 */

/**
 * Include the TGM_Plugin_Activation class.
 */
require_once dirname( __FILE__ ) . '/auto-install.php';

add_action( 'tgmpa_register', 'my_theme_register_required_plugins' );
/**
 * Register the required plugins for this theme.
 *
 * In this example, we register two plugins - one included with the TGMPA library
 * and one from the .org repo.
 *
 * The variable passed to tgmpa_register_plugins() should be an array of plugin
 * arrays.
 *
 * This function is hooked into tgmpa_init, which is fired within the
 * TGM_Plugin_Activation class constructor.
 */
function my_theme_register_required_plugins() {

	$plugins = array(
		array(
			'plugin' => 'tgm-example-plugin/tgm-example-plugin.php', // The main plugin file (including the plugin folder)
			'name'   => 'TGM Example Plugin', // The plugin name
			'source' => get_stylesheet_directory() . '/lib/plugins/tgm-example-plugin.zip', // The plugin source
		),
		array(
			'plugin' => 'edit-howdy/edithowdy.php',
			'name'   => 'Edit Howdy',
			'source' => 'http://downloads.wordpress.org/plugin/edit-howdy.zip',
		),
	);

	tgmpa_register_plugins( $plugins );

}