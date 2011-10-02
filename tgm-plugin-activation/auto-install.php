<?php
/**
 * Plugin installation and activation for WordPress themes.
 *
 * @package	  TGM-Plugin-Activation
 * @version	  2.0.0
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

	/**
	 * Holds a copy of itself, so it can be referenced by the class name.
	 *
	 * @since 1.0.0
	 *
	 * @var TGM_Plugin_Activation
	 */
	static $instance;

	/**
	 * Holds arrays of plugin details.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	var $plugins = array();

	/**
	 * Name of the querystring argument for the admin page.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	var $menu = 'install-required-plugins';

	/**
	 * Text domain for localization support.
	 *
	 * @since 1.1.0
	 *
	 * @var string
	 * @todo Make this value overwritable from outside of the class.
	 */
	var $domain = 'tgmpa';

	/**
	 * Default absolute path to folder containing pre-packaged plugin zip files.
	 *
	 * @since 2.0.0
	 *
	 * @var type
	 * @todo Make this value overwritable from outside of the class.
	 */
	var $default_path = '';

	/**
	 * Holds configurable array of strings.
	 *
	 * Default values are added in the constructor.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	var $strings = array();

	/**
	 * Constructor.
	 *
	 * Adds a reference of this object to $instance, does the tgmpa_init action
	 * hook, and hooks in the interactions to init.
	 *
	 * @since 1.0.0
	 *
	 * @see TGM_Plugin_Activation::init()
	 */
	public function __construct() {

		self::$instance =& $this;

		$this->strings = array(
			'page_title'             => __( 'Install Required Plugins', $this->domain ),
			'menu_title'             => __( 'Install Plugins', $this->domain ),
			'instructions_install'   => __( 'The %1$s plugin is required for this theme. Click on the big blue button below to install and activate %1$s.', $this->domain ),
			'instructions_activate'  => __( 'The %1$s is installed but currently inactive. Please go to the <a href="%2$s">plugin administration page</a> page to activate it.', $this->domain ),
			'button'                 => __( 'Install %s Now', $this->domain ),
			'installing'             => __( 'Installing Plugin: %s', $this->domain ),
			'oops'                   => __( 'Something went wrong.', $this->domain ),
			'notice_can_install'     => __( 'This theme requires the %1$s plugin. <a href="%2$s"><strong>Click here to begin the installation process</strong></a>. You may be asked for FTP credentials based on your server setup.', $this->domain ),
			'notice_cannot_install'  => __( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', $this->domain ),
			'notice_can_activate'    => __( 'This theme requires the %1$s plugin. That plugin is currently inactive, so please go to the <a href="%2$s">plugin administration page</a> to activate it.', $this->domain ),
			'notice_cannot_activate' => __( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', $this->domain ),
			'return'                 => __( 'Return to Required Plugins Installer', $this->domain ),
		);

		/** Annouce that the class is ready, and pass the object (for advanced use) */
		do_action_ref_array( 'tgmpa_init', array( &$this ) );

		/** When the rest of WP has loaded, kick-start the rest of the class */
		add_action( 'init', array( &$this, 'init' ) );

	}

	/**
	 * Initialise the interactions between this class and WordPress.
	 *
	 * Hooks in three new methods for the class: admin_menu, notices and styles.
	 *
	 * @since 2.0.0
	 *
	 * @see TGM_Plugin_Activation::admin_menu()
	 * @see TGM_Plugin_Activation::notices()
	 * @see TGM_Plugin_Activation::styles()
	 */
	public function init() {

		do_action( 'tgmpa_register' );
		/** After this point, the plugins should be registered */

		/** Proceed only if we have plugins to handle */
		if ( $this->plugins ) {

			add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
			add_action( 'admin_notices', array( &$this, 'notices' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'styles' ) );

		}
		add_filter( 'install_plugin_complete_actions', array( &$this, 'actions' ) );

	}

	/**
	 * Adds submenu page under 'Appearance' tab.
	 *
	 * This method adds the submenu page letting users know that a required
	 * plugin needs to be installed.
	 *
	 * This page disappears once the plugin has been installed and activated.
	 *
	 * @since 1.0.0
	 *
	 * @see TGM_Plugin_Activation::init()
	 * @see TGM_Plugin_Activation::install_plugins_page()
	 */
	public function admin_menu() {

		 // Make sure privileges are correct to see the page
		if ( ! current_user_can( 'install_plugins' ) )
			return;

		// Add file_path key for all plugins
		foreach( $this->plugins as $plugin => $values )
			$this->plugins[$plugin]['file_path'] = $this->_get_plugin_basename_from_slug( $values['slug'] );

		foreach ( $this->plugins as $plugin ) {

			if ( ! is_plugin_active( $plugin['file_path'] ) ) {

				add_theme_page(
						$this->strings['page_title'],           // Page title
						$this->strings['menu_title'],           // Menu title
						'edit_theme_options',                   // Capability
						$this->menu,                            // Menu slug
						array( &$this, 'install_plugins_page' ) // Callback
				);
				break;

			}

		}

	}

	/**
	 * Echoes plugin installation form.
	 *
	 * This method is the callback for the admin_menu method function.
	 * This displays the admin page and form area where the user can select to install and activate the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @return null Aborts early if we're processing a submission
	 */
	public function install_plugins_page() {

		if ( $this->do_plugin_install() )
			return;
		?>
		<div class="tgmpa wrap">
		<?php
		screen_icon( 'themes' );
		?>
		<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
		<?php

			$installed_plugins = get_plugins();

			foreach ( $this->plugins as $plugin ) {

				if ( is_plugin_active( $plugin['file_path'] ) ) // If the plugin is active, no need to display the form
					continue;

				if ( ! isset( $installed_plugins[$plugin['file_path']] ) ) { // Plugin is not installed

					echo '<div class="instructions"><p>' . sprintf( $this->strings['instructions_install'], '<strong>' . $plugin['name'] . '</strong>' ) . '</p>'; // Leave <div> tag open, close after the form has been printed

				} elseif ( is_plugin_inactive( $plugin['file_path'] ) ) { // The plugin is installed but not active

					echo '<div class="instructions"><p>' . sprintf( $this->strings['instructions_activate'], '<strong>' . $plugin['name'] . '</strong>', admin_url( 'plugins.php' ) ) . '</p></div>';
					continue; // No need to display a form because it is already installed, just needs to be activated

				}
				?>
				<form action="" method="post">
					<?php
					wp_nonce_field( 'tgmpa', 'tgmpa_nonce' );
					submit_button(
							sprintf(
									$this->strings['button'],
									$plugin['name']
							),                                // Text
							'primary',                        // Type
							sanitize_key( $plugin['name'] ),  // Name
							true,                             // Wrap
							array()                           // Other attributes
					);
					?>
				</form>
				</div><!-- closing div if plugins need to be installed -->
			<?php } ?>
		</div>
		<?php

	}

	/**
	 * Installs and activates the plugin.
	 *
	 * This method actually installs the plugins. It instantiates the
	 * WP_Filesystem Abstraction class to do the heavy lifting.
	 *
	 * Any errors are displayed using the WP_Error class.
	 *
	 * @since 1.0.0
	 *
	 * @uses WP_Filesystem
	 * @uses WP_Error
	 * @uses WP_Upgrader
	 * @uses Plugin_Upgrader
	 * @uses Plugin_Installer_Skin
	 *
	 * @return boolean True on success, false on failure
	 */
	protected function do_plugin_install() {

		if ( empty( $_POST ) ) // Bail out if the global $_POST is empty
			return false;

		check_admin_referer( 'tgmpa', 'tgmpa_nonce' ); // Security check

		foreach ( $this->plugins as $plugin ) { // Iterate and perform the action for each plugin in the array

			$fields = array( sanitize_key( $plugin['name'] ) );
			$method = ''; // Leave blank so WP_Filesystem can populate it as necessary

			if ( isset( $_POST[sanitize_key( $plugin['name'] )] ) ) { // Don't do anything if the form has not been submitted

				$url = wp_nonce_url( 'themes.php?page=' . $this->menu, 'tgm_pa' ); // Make sure we are coming from the right page
				if ( false === ( $creds = request_filesystem_credentials( $url, $method, false, false, $fields ) ) )
					return true;

				if ( ! WP_Filesystem( $creds ) ) {

					request_filesystem_credentials( $url, $method, false, false, $fields ); // Setup WP_Filesystem
					return true;

				}

				require_once ABSPATH . 'wp-admin/includes/plugin-install.php'; // Need for plugins_api
				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php'; // Need for upgrade classes

				$api = plugins_api( 'plugin_information', array( 'slug' => $plugin['slug'], 'fields' => array( 'sections' => false ) ) );

				if ( is_wp_error( $api ) )
					wp_die( $this->strings['oops'] . var_dump( $api ) );

				// Prep variables for Plugin_Installer_Skin class
				$title = sprintf( $this->strings['installing'], $plugin['name'] );
				$nonce = 'install-plugin_' . $plugin['slug'];
				$url = add_query_arg( array( 'action' => 'install-plugin', 'plugin' => $plugin['slug'] ), 'update.php' );
				if ( isset( $_GET['from'] ) )
					$url .= add_query_arg( 'from', urlencode( stripslashes( $_GET['from'] ) ), $url );

				if ( ! isset( $plugin['source'] ) && isset( $api->download_link ) )
					$plugin['source'] = $api->download_link;

				/** Set type, based on whether the source starts with http:// or https:// */
				$type = preg_match('|^http(s)?://|', $plugin['source'] ) ? 'web' : 'upload';

				/** Prefix a default path to pre-packaged plugins */
				$source = ( 'upload' == $type ) ? $this->default_path . $plugin['source'] : $plugin['source'];

				$upgrader = new Plugin_Upgrader( new Plugin_Installer_Skin( compact( 'title', 'url', 'nonce', 'plugin', 'api' ) ) ); // Create a new instance of Plugin_Upgrader

				$upgrader->install( $source ); // Perform the action and install the plugin from the $source URL

			}

		}

		return true;

	}

	/**
	 * Echoes required plugin notice.
	 *
	 * Outputs a message telling users that a specific plugin is required for
	 * their theme. If appropriate, it includes a link to the form page where
	 * users can install and activate the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @global $current_screen
	 * @return null Returns early if we're on the Install page
	 */
	public function notices() {

		global $current_screen;

		// Remove nag on the install pages
		if ( 'appearance_page_' . $this->menu == $current_screen->id )
			return;

		$installed_plugins = get_plugins(); // Retrieve a list of all the plugins

		foreach ( $this->plugins as $plugin ) {

			if ( is_plugin_active( $plugin['file_path'] ) ) // If the plugin is active, no need to display nag
				continue;

			if ( ! isset( $installed_plugins[$plugin['file_path']] ) ) { // Not installed

				if ( current_user_can( 'install_plugins' ) )
					$message = sprintf( $this->strings['notice_can_install'], '<em>' . $plugin['name'] . '</em>', add_query_arg( 'page', $this->menu, admin_url( 'themes.php' ) ) );
				else // Need higher privileges to install the plugin
					$message = sprintf( $this->strings['notice_cannot_install'], '<em>' . $plugin['name'] . '</em>' );

			} elseif ( is_plugin_inactive( $plugin['file_path'] ) ) { // Installed but not active

				if ( current_user_can( 'activate_plugins' ) )
					$message = sprintf( $this->strings['notice_can_activate'], '<em>' . $plugin['name'] . '</em>', admin_url( 'plugins.php' ) );
				else // Need higher privileges to activate the plugin
					$message = sprintf( $this->strings['notice_cannot_activate'], '<em>' . $plugin['name'] . '</em>' );

			}
			//printf( '<div class="updated"><p>%1$s</p></div>', $message );
			add_settings_error( 'tgmpa', 'tgmpa', $message, 'updated' );

		}

		settings_errors( 'tgmpa' );

	}

	/**
	 * Enqueue a style sheet for this admin page.
	 *
	 * @since 1.1.0
	 *
	 * @global $current_screen
	 * @todo Fix path so it looks for the style sheet in the same directory as this file.
	 */
	public function styles() {

		global $current_screen;

		// Only load the CSS file on the Install page
		if ( 'appearance_page_' . $this->menu == $current_screen->id )
			wp_enqueue_style( 'tgmpa-admin', get_stylesheet_directory_uri() . '/lib/tgm-plugin-activation/admin-css.css', array(), '1.1.0' );

	}

	/**
	 * Add individual plugin to our collection of plugins.
	 *
	 * If the required keys are not set, the plugin is not added.
	 *
	 * @since 2.0.0
	 *
	 * @param type $plugin
	 */
	public function register( $plugin ) {

		if ( ! isset( $plugin['slug'] ) || ! isset( $plugin['name'] ) )
			return;

		$this->plugins[] = $plugin;

	}

	/**
	 * Amend default configuration settings.
	 *
	 * @since 2.0.0
	 *
	 * @param array $config
	 */
	public function config( $config ) {

		$keys = array( 'default_path', 'domain', 'menu', 'strings' );

		foreach ( $keys as $key ) {
			if ( isset( $config[$key]) && $config[$key] ) {
				if ( is_array( $config[$key] ) ) {
					foreach ( $config[$key] as $subkey => $value )
						$this->{$key}[$subkey] = $value;
				} else {
					$this->$key = $config[$key];
				}
			}

		}

	}

	/**
	 * Amend action link after plugin installation.
	 *
	 * @since 2.0.0
	 *
	 * @param array $install_actions Existing array of actions
	 * @return array Amended array of actions
	 */
	public function actions( $install_actions ) {

		$install_actions['plugins_page'] = '<a href="' . add_query_arg( 'page', $this->menu, admin_url( 'themes.php' ) ) . '" title="' . esc_attr( $this->strings['return'] ) . '" target="_parent">' . __( 'Return to Required Plugins Installer', $this->domain ) . '</a>';
		return $install_actions;

	}

	/**
	 * Helper function to extract the file path of the plugin file from the
	 * plugin slug, if the plugin is installed.
	 *
	 * @since 2.0.0
	 *
	 * @param string $slug Plugin slug (typically folder name) as provided by the developer
	 * @return string Either file path for plugin if installed, or just the plugin slug
	 */
	protected function _get_plugin_basename_from_slug( $slug ) {

		$keys = array_keys( get_plugins() );
		foreach ( $keys as $key ) {
			if ( preg_match( '|^' . $slug .'|', $key ) )
					return $key;
		}
		return $slug;

	}

}

new TGM_Plugin_Activation;

/**
 * Helper function to register a collection of required plugins.
 *
 * @since 2.0.0
 * @api
 *
 * @param array $plugins An array of plugin arrays
 * @param array $config Optional. An array of configuration values
 */
function tgmpa( $plugins, $config = array() ) {

	foreach ( $plugins as $plugin )
		TGM_Plugin_Activation::$instance->register( $plugin );

	if ( $config )
		TGM_Plugin_Activation::$instance->config( $config );

}