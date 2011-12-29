<?php
/**
 * This file represents an example of the code that themes would use to register
 * the required plugins.
 *
 * It is expected that theme authors would copy and paste this code into their
 * functions.php file, and amend to suit.
 *
 * @package	   TGM-Plugin-Activation
 * @subpackage Example
 * @version	   2.2.0
 * @author	   Thomas Griffin <thomas@thomasgriffinmedia.com>
 * @author	   Gary Jones <gamajo@gamajo.com>
 * @copyright  Copyright (c) 2011, Thomas Griffin
 * @license	   http://opensource.org/licenses/gpl-3.0.php GPL v3
 * @link       https://github.com/thomasgriffin/TGM-Plugin-Activation
 */

/**
 * Include the TGM_Plugin_Activation class.
 */
require_once dirname( __FILE__ ) . '/class-tgm-plugin-activation.php';

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

	/**
	 * Array of plugin arrays. Required keys are name and slug.
	 * If the source is NOT from the .org repo, then source is also required.
	 */
	$plugins = array(

		// This is an example of how to include a plugin pre-packaged with a theme
		array(
			'name'     => 'TGM Example Plugin', // The plugin name
			'slug'     => 'tgm-example-plugin', // The plugin slug (typically the folder name)
			'source'   => get_stylesheet_directory() . '/lib/plugins/tgm-example-plugin.zip', // The plugin source
			'required' => true, // If false, the plugin is only 'recommended' instead of required
		),

		// This is an example of how to include a plugin from the WordPress Plugin Repository
		array(
			'name' => 'BuddyPress',
			'slug' => 'buddypress',
			'required' => false,
		),

	);

	// Change this to your theme text domain, used for internationalising strings
	$theme_text_domain = 'tgmpa';

	/**
	 * Array of configuration settings. Amend each line as needed.
	 * If you want the default strings to be available under your own theme domain,
	 * leave the strings uncommented.
	 * Some of the strings are added into a sprintf, so see the comments at the
	 * end of each line for what each argument will be.
	 */
	$config = array(
		'domain'       		=> $theme_text_domain,         	// Text domain - likely want to be the same as your theme.
		'default_path' 		=> '',                         	// Default absolute path to pre-packaged plugins
		'parent_menu_slug' 	=> 'themes.php', 				// Default parent menu slug
		'parent_url_slug' 	=> 'themes.php', 				// Default parent URL slug
		'menu'         		=> 'install-required-plugins', 	// Menu slug
		'notices'      		=> true,                       	// Show admin notices or not
		'automatic'    		=> false,					   	// Automatically activate plugins after installation or not
		'message' 			=> '',							// Message to output right before the plugins table
		'strings'      		=> array(
			'page_title'                       			=> __( 'Install Required Plugins', $theme_text_domain ),
			'menu_title'                       			=> __( 'Install Plugins', $theme_text_domain ),
			'installing'                       			=> __( 'Installing Plugin: %s', $theme_text_domain ), // %1$s = plugin name
			'oops'                             			=> __( 'Something went wrong with the plugin API.', $theme_text_domain ),
			'notice_can_install_required_singular' 		=> __( 'This theme requires the following plugin: %1$s.', $theme_text_domain ), // %1$s = plugin name
			'notice_can_install_required'      			=> __( 'This theme requires the following plugins: %1$s.', $theme_text_domain ), // %1$s = plugin names
			'notice_can_install_recommended_singular' 	=> __( 'This theme recommends the following plugin: %1$s.', $theme_text_domain ), // %1$s = plugin name
			'notice_can_install_recommended'   			=> __( 'This theme recommends the following plugins: %1$s.', $theme_text_domain ), // %1$s = plugin names
			'notice_cannot_install_singular'  			=> __( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', $this->domain ), // %1$s = plugin name
			'notice_cannot_install'            			=> __( 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.', $theme_text_domain ), // %1$s = plugin names
			'notice_can_activate_required_singular' 	=> __( 'The following required plugin is currently inactive: %1$s.', $theme_text_domain ), // %1$s = plugin name
			'notice_can_activate_required'     			=> __( 'The following required plugins are currently inactive: %1$s.', $theme_text_domain ), // %1$s = plugin names
			'notice_can_activate_recommended_singular' 	=> __( 'The following recommended plugin is currently inactive: %1$s.', $theme_text_domain ), // %1$s = plugin name
			'notice_can_activate_recommended'  			=> __( 'The following recommended plugins are currently inactive: %1$s.', $theme_text_domain ), // %1$s = plugin names
			'notice_cannot_activate_singular' 			=> __( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', $this->domain ), // %1$s = plugin name
			'notice_cannot_activate'           			=> __( 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.', $theme_text_domain ), // %1$s = plugin names
			'notice_ask_to_update_singular' 			=> __( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', $theme_text_domain ), // %1$s = plugin name
			'notice_ask_to_update' 						=> __( 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.', $theme_text_domain ), // %1$s = plugin name
			'notice_cannot_update_singular' 			=> __( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', $this->domain ), // %1$s = plugin name
			'notice_cannot_update' 						=> __( 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.', $this->domain ), // %1$s = plugin names
			'return'                           			=> __( 'Return to Required Plugins Installer', $theme_text_domain ),
			'plugin_activated'                 			=> __( 'Plugin activated successfully.', $theme_text_domain ),
			'complete' 									=> __( 'All plugins installed and activated successfully. %s', $theme_text_domain ) // %1$s = dashboard link
		)
	);

	tgmpa( $plugins, $config );

}