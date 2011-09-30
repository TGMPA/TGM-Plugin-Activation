<?php
/**
 * Plugin installation and activation for WordPress themes.
 *
 * @package	  TGM-Plugin-Activation
 * @version	  1.1.0
 * @author	  Thomas Griffin <thomas@thomasgriffinmedia.com>
 * @copyright Copyright (c) 2011, Thomas Griffin
 * @license	  http://opensource.org/licenses/gpl-3.0.php GPL v3
 * @link      https://github.com/thomasgriffin/TGM-Plugin-Activation
 */

/*
    Copyright 2011  Thomas Griffin  (email : thomas@thomasgriffinmedia.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 3, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Automatic plugin installation and activation class.
 *
 * Creates a way to automatically install and activate plugins from within themes.
 * The plugins can be either pre-packaged or downloaded from the WordPress
 * Plugin Repository.
 *
 * @since 1.0.0
 *
 * @package TGM-Plugin-Activation
 * @author Thomas Griffin <thomas@thomasgriffinmedia.com>
 */
class TGM_Plugin_Activation {

	var $instance; // DO NOT MODIFY THIS

	var $domain = 'tgmpa'; // Sets the textdomain for localization support

	// All of the variables below can be modified to suit your specific plugin.

	var $args = array(
		array(
			'plugin' 	=> 'tgm-example-plugin/tgm-example-plugin.php', // The main plugin file (including the plugin folder)
			'plugin_name' 	=> 'TGM Example Plugin', // The name of your plugin
			'zip_file' 	=> 'tgm-example-plugin.zip', // The name of your zip file (if no zip file, leave empty - see below)
			'repo_file' 	=> '', // The zip file to get from the repo (if no repo file, leave empty - see below for example if in use)
			'input_name' 	=> 'tgm_tpe' // The form submit input name (used for checks and security
		),
		array(
			'plugin' 	=> 'edit-howdy/edithowdy.php',
			'plugin_name' 	=> 'Edit Howdy',
			'zip_file' 	=> '',
			'repo_file' 	=> 'http://downloads.wordpress.org/plugin/edit-howdy.zip',
			'input_name' 	=> 'tgm_eh'
		)
	);

	var $menu = 'install-required-plugins';


	/**
	 * Adds actions for class methods.
	 *
	 * Adds three new methods for the class: admin_menu, admin_notices and admin_print_styles.
	 * admin_notices handles the nag, admin_menu handles the bulk, and admin_print_styles prints the CSS.
	 *
	 * @since 1.0.0
	 *
	 * @author Thomas Griffin <thomas@thomasgriffinmedia.com>
	 */
	public function __construct() {

		$this->instance =& $this;

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'admin_print_styles', array( $this, 'admin_print_styles' ) );

	}


	/**
	 * Adds submenu page under 'Appearance' tab.
	 *
	 * This method adds the submenu page letting users know that a required plugin needs to be installed.
	 * This page disappears once the plugin has been installed and activated.
	 *
	 * @since 1.0.0
	 *
	 * @author Thomas Griffin <thomas@thomasgriffinmedia.com>
	 */
	public function admin_menu() {

		if ( ! current_user_can( 'install_plugins' ) ) // Make sure privileges are correct to see the page
			return;

		foreach ( $this->args as $args ) {

			if ( ! is_plugin_active( $args['plugin'] ) ) {

				add_theme_page( __( 'Install Required Plugins', $this->domain ), __( 'Install Plugins', $this->domain ), 'edit_theme_options', $this->menu, array( $this, 'install_plugins_page' ) );
				break;

			}

		}

	}


	/**
	 * Outputs plugin installation form.
	 *
	 * This method is the callback for the admin_menu method function.
	 * This displays the admin page and form area where the user can select to install and activate the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @author Thomas Griffin <thomas@thomasgriffinmedia.com>
	 */
	public function install_plugins_page() {

		if ( $this->do_plugin_install() )
			return;

		echo '<div id="tgmpa-form" class="wrap">';
			_e( '<h2>Install Required Plugins</h2>', $this->domain );

			$plugins = get_plugins();

			foreach ( $this->args as $form ) {

				if ( is_plugin_active( $form['plugin'] ) ) // If the plugin is active, no need to display the form
					continue;

				if ( ! isset( $plugins[$form['plugin']] ) ) { // Plugin is not installed

					_e( '<div class="instructions"><p>The <strong>' . $form['plugin_name'] . '</strong> plugin is required for this theme. Click on the big blue button below to install and activate <strong>' . $form['plugin_name'] . '</strong>!</p>', $this->domain );

				} elseif ( is_plugin_inactive( $form['plugin'] ) ) { // The plugin is installed but not active

					_e( '<div class="instructions"><p>The <strong>' . $form['plugin_name'] . '</strong> is installed but currently inactive. Please go to the <a href="' . admin_url( 'plugins.php' ) . '">plugin administration page</a> page to activate it.</p></div>', $this->domain );
					continue; // No need to display a form because it is already installed, just needs to be activated

				} ?>

				<form id="tgmpa-go" action="" method="post">
					<?php wp_nonce_field( 'tgm_pa', 'tgm_pa_nonce' );
					echo "<input name='{$form['input_name']}' class='button-primary' type='submit' value='Install {$form['plugin_name']} Now!' />"; ?>
				</form>
				</div><!-- closing div if plugin is not installed -->

			<?php } ?>
		</div>
		<?php

	}


	/**
	 * Installs and activates the plugin.
	 *
	 * This method function actually installs the plugin. It instantiates the WP_Filesystem Abstraction class to do the heavy lifting.
	 * Any errors are displayed using the WP_Error class.
	 *
	 * @since 1.0.0
	 *
	 * @author Thomas Griffin <thomas@thomasgriffinmedia.com>
	 * @uses WP_Filesystem
	 * @uses WP_Error
	 * @uses WP_Upgrader
	 * @uses Plugin_Upgrader
	 * @uses Plugin_Installer_Skin
	 * @return boolean, true on success, false on failure
	 */
	public function do_plugin_install() {

		foreach ( $this->args as $instance ) { // Iterate and perform the action for each plugin in the array

			if ( empty( $_POST ) ) // Bail out if the global $_POST is empty
				return false;

			check_admin_referer( 'tgm_pa', 'tgm_pa_nonce' ); // Security check

			$fields = array( $instance['input_name'] );
			$method = ''; // Leave blank so WP_Filesystem can populate it as necessary

			if ( isset( $_POST[$instance['input_name']] ) ) { // Don't do anything if the form has not been submitted

				$url = wp_nonce_url( 'themes.php?page=' . $this->menu . '', 'tgm_pa' ); // Make sure we are coming from the right page
				if ( false === ( $creds = request_filesystem_credentials( $url, $method, false, false, $fields ) ) )
					return true;

				if ( ! WP_Filesystem( $creds ) ) {

					request_filesystem_credentials( $url, $method, false, false, $fields ); // Setup WP_Filesystem
					return true;

				}

				global $wp_filesystem; // Introduce global $wp_filesystem to use WP_Filesystem class methods

				// This section is specifically for plugins pre-packaged with the theme
				if ( isset( $instance['zip_file'] ) && '' !== $instance['zip_file'] ) {

					$source = get_stylesheet_directory() . '/lib/tgm-plugin-activation/plugins/' . $instance['zip_file']; // The source zip file

					include_once ABSPATH . 'wp-admin/includes/plugin-install.php'; // Need for plugins_api
					include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php'; // Need for upgrade classes

					$api = plugins_api( 'plugin_information', array( 'slug' => $instance['plugin'], 'fields' => array( 'sections' => false ) ) );

					if ( is_wp_error( $api ) )
	 					wp_die( $api );

					// Prep variables for Plugin_Installer_Skin class
					$title = sprintf( __( 'Installing Plugin: %s'), $instance['plugin_name'], $this->domain );
					$nonce = 'install-plugin_' . $instance['plugin'];
					$url = 'update.php?action=install-plugin&plugin=' . $instance['plugin'];
					if ( isset( $_GET['from'] ) )
						$url .= '&from=' . urlencode( stripslashes( $_GET['from'] ) );

					$type = 'upload'; // Important distinction

					$upgrader = new Plugin_Upgrader( new Plugin_Installer_Skin( compact( 'title', 'url', 'nonce', 'plugin', 'api' ) ) ); // Create a new instance of Plugin_Upgrader

					$upgrader->install( $source ); // Perform the action and install the plugin from the $source URL

					if ( is_wp_error( $upgrader ) ) { // Spit out an error if any exists
						$upgrader_error = $upgrader->get_error_message();
						echo '<div id="message" class="installation error"><p>' . $upgrader_error . '</p></div>';
						return false;
					}

				}

				// This section is specifically for plugins automatically downloaded from the WordPress Plugin Repository
				else {

					include_once ABSPATH . 'wp-admin/includes/plugin-install.php'; // Need for plugins_api
					include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php'; // Need for upgrade classes

					$api = plugins_api( 'plugin_information', array( 'slug' => $instance['plugin'], 'fields' => array( 'sections' => false ) ) );

					if ( is_wp_error( $api ) )
	 					wp_die( $api );

					// Prep variables for Plugin_Installer_Skin class
					$title = sprintf( __( 'Installing Plugin: %s'), $instance['plugin_name'], $this->domain );
					$nonce = 'install-plugin_' . $instance['plugin'];
					$url = 'update.php?action=install-plugin&plugin=' . $instance['plugin'];
					if ( isset( $_GET['from'] ) )
						$url .= '&from=' . urlencode( stripslashes( $_GET['from'] ) );

					$type = 'web'; // Set this to web for tailored output messages

					$upgrader = new Plugin_Upgrader( new Plugin_Installer_Skin( compact( 'title', 'url', 'nonce', 'plugin', 'api' ) ) ); // Create a new instance of Plugin_Upgrader

					$upgrader->install( $instance['repo_file'] ); // Perform the action and install the plugin from the Repository

					if ( is_wp_error( $upgrader ) ) { // Spit out an error if any exists
						$upgrader_error = $upgrader->get_error_message();
						echo '<div id="message" class="installation error"><p>' . $upgrader_error . '</p></div>';
						return false;
					}

				}

			}

		}

		return true;

	}


	/**
	 * Display required notice nag.
	 *
	 * Outputs a message telling users that a specific plugin is required for their theme.
	 * Displays a link to the form page where users can install and activate the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @author Thomas Griffin <thomas@thomasgriffinmedia.com>
	 * @author Gary Jones
	 * @global $current_screen
	 * @param string $output
	 * @return string HTML markup
	 */
	public function admin_notices( $output ) {

		global $current_screen;

		if ( 'appearance_page_install-required-plugins' == $current_screen->id ) // Remove nag on the install pages
			return;

		$plugins = get_plugins(); // Retrieve a list of all the plugins

		foreach ( $this->args as $args ) {

			if ( ! isset( $plugins[$args['plugin']] ) ) { // Not installed

				if ( current_user_can( 'install_plugins' ) ) {

					$message = sprintf( __( 'This theme requires the <strong>' . $args['plugin_name'] . '</strong> plugin. <a href="%s"><strong>Click here to begin the installation process</strong></a>. You may be asked for FTP credentials based on your server setup.', $this->domain ), admin_url( 'themes.php?page=' . $this->menu . '' ) );
					$output = printf( '<div id="tgm-plugin-activation" class="updated"><p>%1$s</p></div>', $message );

				} else { // Need higher privileges to install the plugin

					$message = sprintf( __( 'Sorry, but you do not have the correct permissions to install the <strong>' . $args['plugin_name'] . '</strong> plugin. Contact the administrator of this site for help on getting the plugin installed.', $this->domain ) );
					$output = printf( '<div id="tgm-plugin-activation" class="updated"><p>%1$s</p></div>', $message );

				}


			} elseif ( is_plugin_inactive( $args['plugin'] ) ) { // Installed but not active

				if ( current_user_can( 'activate_plugins' ) ) {

					$message = sprintf( __( 'This theme requires the <strong>' . $args['plugin_name'] . '</strong> plugin. The <strong>' . $args['plugin_name'] . '</strong> plugin is currently inactive, so please go to the <a href="%s"><strong>plugin administration page</strong></a> to activate it.', $this->domain ), admin_url( 'plugins.php' ) );
					$output = printf( '<div id="tgm-plugin-inactive" class="updated"><p>%1$s</p></div>', $message );

				} else { // Need higher privileges to activate the plugin

					$message = sprintf( __( 'Sorry, but you do not have the correct permissions to activate the <strong>' . $args['plugin_name'] . '</strong> plugin. Contact the administrator of this site for help on getting the plugin activated.', $this->domain ) );
					$output = printf( '<div id="tgm-plugin-inactive" class="updated"><p>%1$s</p></div>', $message );

				}

			}

		}

	}


	/**
	 * Print admin stylesheet.
	 *
	 * Prints an admin stylesheet to format the input forms
	 *
	 * @since 1.1.0
	 *
	 * @author Thomas Griffin <thomas@thomasgriffinmedia.com>
	 * @global $current_screen
	 * @param string $output
	 * @return string HTML markup
	 */
	public function admin_print_styles() {

		global $current_screen;

		if ( 'appearance_page_install-required-plugins' == $current_screen->id ) // Only load the CSS file on our page
			wp_enqueue_style( 'tgmpa-admin', get_stylesheet_directory_uri() . '/lib/tgm-plugin-activation/admin-css.css' );

	}

}

new TGM_Plugin_Activation(); // Instantiate a new instance of the class