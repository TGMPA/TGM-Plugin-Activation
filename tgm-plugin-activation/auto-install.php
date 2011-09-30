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
	
	var $args = array(
		array( 'plugin' => 'tgm-example-plugin/tgm-example-plugin.php', 'plugin_name' => 'TGM Example Plugin', 'zip_file' => 'tgm-example-plugin.zip', 'repo_file' => '', 'input_name' => 'tgm_tpe', 'nonce_name' => 'tgm_tpe' ),
		array( 'plugin' => 'edit-howdy/edithowdy.php', 'plugin_name' => 'Edit Howdy', 'zip_file' => '', 'repo_file' => 'http://downloads.wordpress.org/plugin/edit-howdy.zip', 'input_name' => 'tgm_eh', 'nonce_name' => 'tgm_eh' )
	);
	var $menu = 'install-required-plugins';
	
	
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

		if ( $this->tgm_do_plugin_install() )
			return;
		
		?>
		<div class="wrap">
			<h2><?php _e( 'Install Required Plugins', 'tgmpa' ); ?></h2>
			<?php foreach ( $this->args as $form ) {
				if ( is_plugin_active( $form['plugin'] ) )
					continue; ?>
				<p><?php _e( 'The ' . $form['plugin_name'] . ' plugin is required for this theme. Click on the big blue button below to install and activate ' . $form['plugin_name'] . '!', 'tgmpa' ); ?></p>
				<form action="" method="post">
					<?php wp_nonce_field( $form['nonce_name'], 'tgm_pa' );
					echo "<input name='{$form['input_name']}' class='button-primary' type='submit' value='Install {$form['plugin_name']} Now!' />"; ?>
				</form>
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
	 * @access public
	 * @uses WP_Filesystem
	 * @uses WP_Error
	 * @return boolean, true on success, false on failure
	 */
	public function tgm_do_plugin_install() {
	
		foreach ( $this->args as $instance ) {
	
			if ( empty( $_POST ) ) // Bail out if the global $_POST is empty
				return false;
			
			check_admin_referer( $instance['nonce_name'], 'tgm_pa' );
	
			$fields = array( $instance['input_name'] );
			$method = ''; // Leave blank so WP_Filesystem can populate it as necessary
	
			if ( isset( $_POST[$instance['input_name']] ) ) { // Don't do anything if the form has not been submitted
	
				$url = wp_nonce_url( 'themes.php?page=' . $this->menu . '', $instance['nonce_name'] ); // Make sure we are coming from the right page
				if ( false === ( $creds = request_filesystem_credentials( $url, $method, false, false, $fields ) ) )
					return true;
		
				if ( ! WP_Filesystem( $creds ) ) {
		
					request_filesystem_credentials( $url, $method, false, false, $fields ); // Setup WP_Filesystem
					return true;
		
				}
		
				global $wp_filesystem; // Introduce global $wp_filesystem to use WP_Filesystem class methods
				
				if ( isset( $instance['zip_file'] ) && '' !== $instance['zip_file'] ) {
		
					$source = get_stylesheet_directory() . '/lib/tgm-plugin-activation/plugins/' . $instance['zip_file']; // The source zip file
					
					include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
					include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
					
					$api = plugins_api( 'plugin_information', array( 'slug' => $instance['plugin'], 'fields' => array( 'sections' => false ) ) );

					if ( is_wp_error( $api ) )
	 					wp_die( $api );

					$title = sprintf( __( 'Installing Plugin: %s'), $instance['plugin_name'] );
					$nonce = 'install-plugin_' . $instance['plugin'];
					$url = 'update.php?action=install-plugin&plugin=' . $instance['plugin'];
					if ( isset( $_GET['from'] ) )
						$url .= '&from=' . urlencode( stripslashes( $_GET['from'] ) );

					$type = 'upload'; //Install plugin type, From Web or an Upload.

					$upgrader = new Plugin_Upgrader( new Plugin_Installer_Skin( compact( 'title', 'url', 'nonce', 'plugin', 'api' ) ) );
					$upgrader->install( $source );
					
					if ( is_wp_error( $upgrader ) ) {
						$upgrader_error = $upgrader->get_error_message();
						echo '<div id="message" class="installation error"><p>' . $upgrader_error . '</p></div>';
						return false;
					}
					
				}
				
				else {
				
					include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
					include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
					
					$api = plugins_api( 'plugin_information', array( 'slug' => $instance['plugin'], 'fields' => array( 'sections' => false ) ) );

					if ( is_wp_error( $api ) )
	 					wp_die( $api );

					$title = sprintf( __( 'Installing Plugin: %s'), $instance['plugin_name'] );
					$nonce = 'install-plugin_' . $instance['plugin'];
					$url = 'update.php?action=install-plugin&plugin=' . $instance['plugin'];
					if ( isset( $_GET['from'] ) )
						$url .= '&from=' . urlencode( stripslashes( $_GET['from'] ) );

					$type = 'web'; //Install plugin type, From Web or an Upload.

					$upgrader = new Plugin_Upgrader( new Plugin_Installer_Skin( compact( 'title', 'url', 'nonce', 'plugin', 'api' ) ) );
					$upgrader->install( $instance['repo_file'] );
					
					if ( is_wp_error( $upgrader ) ) {
						$upgrader_error = $upgrader->get_error_message();
						echo '<div id="message" class="installation error"><p>' . $upgrader_error . '</p></div>';
						return false;
					}
				
				}
			
			}
		
			if ( is_plugin_active( $instance['plugin'] ) ) { // Display a message upon successful activation of the plugin
			
				printf( '<div class="wrap"><h2>Congratulations!</h2><div class="updated"><p>Congratulations! ' .  $instance['plugin_name'] . ' has been successfully installed, activated and is ready for use. <a href="%1$s">Return to the dashboard.</a></p></div>', admin_url(), 'tgmpa' );
			
			}
	
			return true;
			
		}
	
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
		
		foreach ( $this->args as $args ) {
		
			if ( ! is_plugin_active( $args['plugin'] ) && ! isset( $_POST[$args['input_name']] ) ) {
			
				$message = sprintf( __( 'This theme requires the ' . $args['plugin_name'] . ' plugin. <a href="%s"><strong>Click here to begin the installation process</strong></a>. You may be asked for FTP credentials based on your server setup.', 'tgmpa' ), admin_url( 'themes.php?page=' . $this->menu . '' ) );
				$output = printf( '<div id="tgm-plugin-activation" class="updated"><p>%1$s</p></div>', $message );
			
			}
			
		}
		
	}

}

new TGM_Plugin_Activation(); // Instantiate a new instance of the class