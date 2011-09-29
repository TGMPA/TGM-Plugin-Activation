<?php
/**
 * Plugin Installation and Activation for WordPress Themes
 *
 * @package			TGM Plugin Activation
 * @version			1.0.0
 * @author			Thomas Griffin <http://thomasgriffinmedia.com/>
 * @copyright		Copyright (c) 2011, Thomas Griffin
 * @license			http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @link			https://github.com/thomasgriffin/TGM-Plugin-Activation
 *
 */
 
 
/*  Copyright 2011  Thomas Griffin  (email : thomas@thomasgriffinmedia.com)

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


if ( ! is_admin() ) // If we are not in the admin section, bail out
	return;


class TGM_Plugin_Activation {
	
	var $instance; // DO NOT MODIFY THIS
	
	// All of the variables below can be modified to suit your specific plugin. 
	
	var $plugin 		= 'tgm-example-plugin/tgm-example-plugin.php'; // The main plugin file (you must include the plugin folder)
	var $plugin_name 	= 'TGM Example Plugin'; // The name of your plugin
	var $zip			= 'tgm-example-plugin.zip'; // The name of the zip file that contains your plugin
	var $menu 			= 'tgm-example-plugin'; // The name of your menu page (will appear in the URL as themes.php?page=tgm-example-plugin)
	var $nonce 			= 'tgm_plugins'; // You can set your own nonce name if you would
	
	
	/**
	 * Adds actions for class methods.
	 *
	 * Adds two new methods for the class: admin_menu and admin_notices. 
	 * admin_notices handles the nag; admin_menu handles the rest.
	 * 
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function __construct() {
	
		$this->instance =& $this;

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	
	}
	
	
	/**
	 * Adds submenu page under 'Appearance' tab.
	 *
	 * This method adds the submenu page letting users know that a required plugin needs to be installed. 
	 * This page disappears once the plugin has been installed and activated.
	 * 
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function admin_menu() {
		
		if ( is_plugin_active( $this->plugin ) ) // No need to output the page if our plugin is already installed and activated
			return;
			
		add_submenu_page( 'themes.php', __( 'Install Required Plugins', 'tgmpa' ), __( 'Install Plugins', 'tgmpa' ), 'edit_theme_options', $this->menu, array( $this, 'tgm_install_plugins_page' ) );
	
	}
	
	
	/**
	 * Outputs plugin installation form.
	 *
	 * This method is the callback for the admin_menu method function. 
	 * This displays the admin page and form area where the user can select to install and activate the plugin.
	 * 
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function tgm_install_plugins_page() {
	
		if ( $this->tgm_do_plugin_install() ) // No need to output the form if tgm_do_plugin_install has been successfully run
			return;

		?>
		<div class="wrap">
			<h2><?php _e( 'Install Required Plugins', 'tgmpa' ); ?></h2>
			<p><?php _e( 'The ' . $this->plugin_name . ' plugin is required for this theme. Click on the big blue button below to install and activate ' . $this->plugin_name . '!', 'tgmpa' ); ?></p>
			<form action="" method="post">
				<?php wp_nonce_field( $this->nonce );
				echo "<input name='tgm_go' class='button-primary' type='submit' value='Install {$this->plugin_name} Now!' />"; ?>
			</form>
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
	 * @access public
	 * @uses WP_Filesystem
	 * @uses WP_Error
	 * @return boolean, true on success, false on failure
	 */
	public function tgm_do_plugin_install() {
	
		if ( empty( $_POST ) ) // Bail out if the global $_POST is empty
			return false;
			
		if ( is_plugin_active( $this->plugin ) ) // Bail out if our plugin is already activated
			return false;
	
		check_admin_referer( $this->nonce );
	
		$fields = array( 'tgm_go' );
		$method = ''; // Leave blank so WP_Filesystem can populate it as necessary
	
		if ( isset( $_POST['tgm_go'] ) ) { // Don't do anything if the form has not been submitted
	
			$url = wp_nonce_url( 'themes.php?page=' . $this->menu . '', $this->nonce ); // Make sure we are coming from the right page
			if ( false === ( $creds = request_filesystem_credentials( $url, $method, false, false, $fields ) ) )
				return true;
		
			if ( ! WP_Filesystem( $creds ) ) {
		
				request_filesystem_credentials( $url, $method, false, false, $fields ); // Setup WP_Filesystem
				return true;
		
			}
		
			global $wp_filesystem; // Introduce global $wp_filesystem to use WP_Filesystem class methods
		
			$source = get_stylesheet_directory() . '/lib/tgm-plugin-activation/plugins/' . $this->zip; // The source zip file
			$destination = $wp_filesystem->wp_plugins_dir(); // The destination for where we want our plugin installed (wp-content/plugins)
			
			$install = unzip_file( $source, $destination ); // Use WP_Filesystem to unzip file to our destination directory (wp-content/plugins)
			
			if ( is_wp_error ( $install ) ) { // Spit out an error message if the installation fails
				$install_error = $install->get_error_message();
				echo '<div id="message" class="installation error"><p>' . $install_error . '</p></div>';
				return false;
			}
			
			if ( $wp_filesystem->is_dir( $destination ) && ! is_plugin_active( $this->plugin ) )	
				$activate = activate_plugin( $this->plugin, '', '' ); // Activate our plugin if it exists
				
			if ( is_wp_error( $activate ) ) { // Spit out an error message if the activation fails
				$activate_error = $activate->get_error_message();
				echo '<div id="message" class="activation error"><p>' . $activate_error . '</p></div>';
				return false;
			} 
			
		}
		
		if ( is_plugin_active( $this->plugin ) ) { // Display a message upon successful activation of the plugin
			
			printf( '<div class="wrap"><h2>Congratulations!</h2><div class="updated"><p>Congratulations! ' .  $this->plugin_name . ' has been successfully installed, activated and is ready for use. <a href="%1$s">Return to the dashboard.</a></p></div>', admin_url(), 'tgmpa' );
			
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
	 * @access public
	 * @param string $output
	 * @return string HTML markup
	 */
	public function admin_notices( $output ) {
		
		if ( ! is_plugin_active( $this->plugin ) && ! isset( $_POST['tgm_go'] ) ) {
			
			$message = sprintf( __( 'This theme requires the ' . $this->plugin_name . ' plugin. <a href="%s"><strong>Click here to begin the installation process</strong></a>. You may be asked for FTP credentials based on your server setup.', 'tgmpa' ), admin_url( 'themes.php?page=' . $this->menu . '' ) );
			$output = printf( '<div id="tgm-plugin-activation" class="updated"><p>%1$s</p></div>', $message );
			
		}
		
	}

}

new TGM_Plugin_Activation(); // Instantiate a new instance of the class