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
 * @version	   2.1.1
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
		/** This is an example of how to include a plugin pre-packaged with a theme */
		array(
			'name'     => 'TGM Example Plugin', // The plugin name
			'slug'     => 'tgm-example-plugin', // The plugin slug (typically the folder name)
			'source'   => get_stylesheet_directory() . '/lib/plugins/tgm-example-plugin.zip', // The plugin source
			'required' => true // If false, the plugin is only 'recommended' instead of required
		),
		/** This is an example of how to include a plugin from the WordPress Plugin Repository */
		array(
			'name' => 'BuddyPress',
			'slug' => 'buddypress',
			'required' => false
		)
	);

	/** Change this to your theme text domain, used for internationalising strings */
	$theme_text_domain = 'tgmpa';

	/**
	 * Array of configuration settings. Uncomment and amend each line as needed.
	 * If you want the default strings to be available under your own theme domain,
	 * uncomment the strings and domain.
	 * Some of the strings are added into a sprintf, so see the comments at the
	 * end of each line for what each argument will be.
	 */
	$config = array(
		/*'domain'       => $theme_text_domain,         // Text domain - likely want to be the same as your theme. */
		/*'default_path' => '',                         // Default absolute path to pre-packaged plugins */
		/*'menu'         => 'install-required-plugins', // Menu slug */
		/*'notices'      => true,                       // Show admin notices or not */
		'strings'      => array(
			/*'page_title'             				=> __( 'Install Required Plugins', $theme_text_domain ), // */
			/*'menu_title'             				=> __( 'Install Plugins', $theme_text_domain ), // */
			/*'instructions_install'   				=> __( 'The %1$s plugin is required for this theme. Click on the big blue button below to install and activate %1$s.', $theme_text_domain ), // %1$s = plugin name */
			/*'instructions_install_recommended'	=> __( 'The %1$s plugin is recommended for this theme. Click on the big blue button below to install and activate %1$s.', $theme_text_domain ), // %1$s = plugin name, %2$s = plugins page URL */
			/*'instructions_activate'  				=> __( 'The %1$s plugin is installed but currently inactive. Please go to the <a href="%2$s">plugin administration page</a> page to activate it.', $theme_text_domain ), // %1$s = plugin name, %2$s = plugins page URL */
			/*'button'                 				=> __( 'Install %s Now', $theme_text_domain ), // %1$s = plugin name */
			/*'installing'             				=> __( 'Installing Plugin: %s', $theme_text_domain ), // %1$s = plugin name */
			/*'oops'                   				=> __( 'Something went wrong with the plugin API.', $theme_text_domain ), // */
			/*'notice_can_install_required'     	=> __( 'This theme requires the following plugins: %1$s.', $theme_text_domain ), // %1$s = plugin names */
			/*'notice_can_install_recommended'		=> __( 'This theme recommends the following plugins: %1$s.', $theme_text_domain ), // %1$s = plugin names */
			/*'notice_cannot_install'  				=> __( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', $theme_text_domain ), // %1$s = plugin name */
			/*'notice_can_activate_required'    	=> __( 'The following required plugins are currently inactive: %1$s.', $theme_text_domain ), // %1$s = plugin names */
			/*'notice_can_activate_recommended'		=> __( 'The following recommended plugins are currently inactive: %1$s.', $theme_text_domain ), // %1$s = plugin names */
			/*'notice_cannot_activate' 				=> __( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', $theme_text_domain ), // %1$s = plugin name */
			/*'return'                 				=> __( 'Return to Required Plugins Installer', $theme_text_domain ), // */
			/*'plugin_activated' 	   				=> __( 'Plugin activated successfully.', $theme_text_domain ) // */
		)
	);

	tgmpa( $plugins, $config );

}