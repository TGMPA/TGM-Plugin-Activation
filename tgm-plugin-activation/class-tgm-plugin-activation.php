<?php
/**
 * Plugin installation and activation for WordPress themes.
 *
 * @package   TGM-Plugin-Activation
 * @version   2.2.0
 * @author    Thomas Griffin <thomas@thomasgriffinmedia.com>
 * @author    Gary Jones <gamajo@gamajo.com>
 * @copyright Copyright (c) 2011, Thomas Griffin
 * @license   http://opensource.org/licenses/gpl-3.0.php GPL v3
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
 * @author Gary Jones <gamajo@gamajo.com>
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
	 */
	var $domain = 'tgmpa';

	/**
	 * Default absolute path to folder containing pre-packaged plugin zip files.
	 *
	 * @since 2.0.0
	 *
	 * @var string Absolute path prefix to packaged zip file location. Default is empty string.
	 */
	var $default_path = '';

	/**
	 * Flag to show admin notices or not.
	 *
	 * @since 2.1.0
	 *
	 * @var boolean
	 */
	var $notices = true;

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
	 * Adds a reference of this object to $instance, populates default strings,
	 * does the tgmpa_init action hook, and hooks in the interactions to init.
	 *
	 * @since 1.0.0
	 *
	 * @see TGM_Plugin_Activation::init()
	 */
	public function __construct() {

		self::$instance =& $this;

		$this->strings = array(
			'parent_slug'					=> 'themes.php',
			'page_title'             			=> __( 'Install Required Plugins', $this->domain ),
			'menu_title'             			=> __( 'Install Plugins', $this->domain ),
			'instructions_install'   			=> __( 'The %1$s plugin is required for this theme. Click on the big blue button below to install and activate %1$s.', $this->domain ),
			'instructions_install_recommended'	=> __( 'The %1$s plugin is recommended for this theme. Click on the big blue button below to install and activate %1$s.', $this->domain ),
			'instructions_activate'  			=> __( 'The %1$s plugin is installed but currently inactive. Please go to the <a href="%2$s">plugin administration page</a> page to activate it.', $this->domain ),
			'button'                 			=> __( 'Install %s Now', $this->domain ),
			'installing'             			=> __( 'Installing Plugin: %s', $this->domain ),
			'oops'                   			=> __( 'Something went wrong.', $this->domain ),
			'notice_can_install_required'     	=> __( 'This theme requires the following plugins: %1$s.', $this->domain ),
			'notice_can_install_recommended'	=> __( 'This theme recommends the following plugins: %1$s.', $this->domain ),
			'notice_cannot_install'  			=> __( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', $this->domain ),
			'notice_can_activate_required'    	=> __( 'The following required plugins are currently inactive: %1$s.', $this->domain ),
			'notice_can_activate_recommended'	=> __( 'The following recommended plugins are currently inactive: %1$s.', $this->domain ),
			'notice_cannot_activate' 			=> __( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', $this->domain ),
			'return'                 			=> __( 'Return to Required Plugins Installer', $this->domain ),
			'plugin_activated' 					=> __( 'Plugin activated successfully.', $this->domain )
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
		/** After this point, the plugins should be registered and the configuration set */

		/** Proceed only if we have plugins to handle */
		if ( $this->plugins ) {

			$sorted = array(); // Prepare variable for sorting

			foreach ( $this->plugins as $plugin )
				$sorted[] = $plugin['name'];

			array_multisort( $sorted, SORT_ASC, $this->plugins ); // Sort plugins alphabetically by name

			add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
			add_action( 'admin_head', array( &$this, 'dismiss' ) );
			add_filter( 'install_plugin_complete_actions', array( &$this, 'actions' ) );
			
			/** This fixes the admin bar not loading until after installation/activation is complete */
			if ( $this->is_tgmpa_page() ) {
				remove_action( 'wp_footer', 'wp_admin_bar_render', 1000 );
				remove_action( 'admin_footer', 'wp_admin_bar_render', 1000 );
				add_action( 'wp_head', 'wp_admin_bar_render', 1000 );
				add_action( 'admin_head', 'wp_admin_bar_render', 1000 );
			}

			if ( $this->notices ) {
				add_action( 'admin_notices', array( &$this, 'notices' ) );
				add_action( 'admin_init', array( &$this, 'admin_init' ), 1 );
				add_action( 'admin_enqueue_scripts', array( &$this, 'thickbox' ) );
				add_action( 'switch_theme', array( &$this, 'update_dismiss' ) );
			}

		}

	}

	/**
	 * Handles calls to show plugin information via links in the notices.
	 *
	 * We get the links in the admin notices to point to the TGMPA page, rather
	 * than the typical plugin-install.php file, so we can prepare everything
	 * beforehand.
	 *
	 * WP doesn't make it easy to show the plugin information in the thickbox -
	 * here we have to require a file that includes a function that does the
	 * main work of displaying it, enqueue some styles, set up some globals and
	 * finally call that function before exiting.
	 *
	 * Down right easy once you know how...
	 *
	 * @since 2.1.0
	 *
	 * @global string $tab Used as iframe div class names, helps with styling
	 * @global string $body_id Used as the iframe body ID, helps with styling
	 * @return null Returns early if not the TGMPA page.
	 */
	public function admin_init() {
		
		if ( ! $this->is_tgmpa_page() )
			return;

		if ( isset( $_REQUEST['tab'] ) && 'plugin-information' == $_REQUEST['tab'] ) {

			require_once ABSPATH . 'wp-admin/includes/plugin-install.php'; // Need for install_plugin_information()

			wp_enqueue_style( 'plugin-install' );

			global $tab, $body_id;
			$body_id = $tab = 'plugin-information';

			install_plugin_information();

			exit;

		}

	}

	/**
	 * Enqueues thickbox scripts/styles for plugin info.
	 *
	 * Thickbox is not automatically included on all admin pages, so we must
	 * manually enqueue it for those pages.
	 *
	 * Thickbox is only loaded if the user has not dismissed the admin
	 * notice or if there are any plugins left to install and activate.
	 *
	 * @since 2.1.0
	 */
	public function thickbox() {

		if ( ! get_user_meta( get_current_user_id(), 'tgmpa_dismissed_notice', true ) )
				add_thickbox();

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

		$this->populate_file_path();

		foreach ( $this->plugins as $plugin ) {

			if ( ! is_plugin_active( $plugin['file_path'] ) ) {

				add_submenu_page(
						$this->strings['parent_slug'],		// Position in the wp-admin
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
				
			$plugin_table = new TGMPA_List_Table;
			$plugin_table->prepare_items();

			?>
			<form id="tgmpa-plugins" action="" method="post">
            	<input type="hidden" name="page" value="<?php echo $this->menu; ?>" />
            	<?php $plugin_table->display(); ?>
        	</form>
        	
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
		
		$plugin = array(); 
	
		if ( isset( $_GET[sanitize_key( 'plugin' )] ) && ( isset( $_GET[sanitize_key( 'tgmpa-install' )] ) && 'install-plugin' == $_GET[sanitize_key( 'tgmpa-install' )] ) ) {
			
			check_admin_referer( 'tgmpa-install' );
			
			$plugin['name'] = $_GET[sanitize_key( 'plugin_name' )];
			$plugin['slug'] = $_GET[sanitize_key( 'plugin' )];
			$plugin['source'] = $_GET[sanitize_key( 'plugin_source' )];
			
			$method = ''; // Leave blank so WP_Filesystem can populate it as necessary

			$url = wp_nonce_url( 'themes.php?page=' . $this->menu, 'tgmpa-install' ); // Make sure we are coming from the right page
			if ( false === ( $creds = request_filesystem_credentials( $url, $method, false, false ) ) )
				return true;

			if ( ! WP_Filesystem( $creds ) ) {

				request_filesystem_credentials( $url, $method, true, false ); // Setup WP_Filesystem
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

			if ( isset( $plugin['source'] ) && 'repo' == $plugin['source'] && isset( $api->download_link ) )
				$plugin['source'] = $api->download_link;

			/** Set type, based on whether the source starts with http:// or https:// */
			$type = preg_match('|^http(s)?://|', $plugin['source'] ) ? 'web' : 'upload';

			/** Prefix a default path to pre-packaged plugins */
			$source = ( 'upload' == $type ) ? $this->default_path . $plugin['source'] : $plugin['source'];

			$upgrader = new Plugin_Upgrader( new Plugin_Installer_Skin( compact( 'title', 'url', 'nonce', 'plugin', 'api' ) ) ); // Create a new instance of Plugin_Upgrader

			$upgrader->install( $source ); // Perform the action and install the plugin from the $source urldecode()

			$plugin_activate = $upgrader->plugin_info(); // Grab the plugin info from the Plugin_Upgrader method

			wp_cache_flush(); // Flush the cache to remove plugin header errors

			$activate = activate_plugin( $plugin_activate ); // Activate the plugin

			$this->populate_file_path(); // Re-populate the file path now that the plugin has been installed and activated

			if ( is_wp_error( $activate ) ) {
				echo '<div id="message" class="error"><p>' . $activate->get_error_message() . '</p></div>';
				echo '<p><a href="' . add_query_arg( 'page', $this->menu, admin_url( 'themes.php' ) ) . '" title="' . esc_attr( $this->strings['return'] ) . '" target="_parent">' . __( 'Return to Required Plugins Installer', $this->domain ) . '</a></p>';
				return true; // End it here if there is an error with automatic activation
			}
			else {
				echo '<p>' . $this->strings['plugin_activated'] . '</p>';

				foreach ( $this->plugins as $plugin ) {

					if ( ! is_plugin_active( $plugin['file_path'] ) ) {

						echo '<p><a href="' . add_query_arg( 'page', $this->menu, admin_url( 'themes.php' ) ) . '" title="' . esc_attr( $this->strings['return'] ) . '" target="_parent">' . __( 'Return to Required Plugins Installer', $this->domain ) . '</a></p>';
						break;

					}

				}

			}

			return true;
		
		}
		elseif ( isset( $_GET[sanitize_key( 'plugin' )] ) && ( isset( $_GET[sanitize_key( 'tgmpa-activate' )] ) && 'activate-plugin' == $_GET[sanitize_key( 'tgmpa-activate' )] ) ) {
		
			check_admin_referer( 'tgmpa-activate' );
			
			$plugin['name'] = $_GET[sanitize_key( 'plugin_name' )];
			$plugin['slug'] = $_GET[sanitize_key( 'plugin' )];
			$plugin['source'] = $_GET[sanitize_key( 'plugin_source' )];
			
			$plugin_data = get_plugins( '/' . $plugin['slug'] );
			
			$plugin_file = array_keys( $plugin_data );
			
			$plugin_to_activate = $plugin['slug'] . '/' . $plugin_file[0];
			
			$activate = activate_plugin( $plugin_to_activate );
		
			if ( is_wp_error( $activate ) ) {
				echo '<div id="message" class="error"><p>' . $activate->get_error_message() . '</p></div>';
				echo '<p><a href="' . add_query_arg( 'page', $this->menu, admin_url( 'themes.php' ) ) . '" title="' . esc_attr( $this->strings['return'] ) . '" target="_parent">' . __( 'Return to Required Plugins Installer', $this->domain ) . '</a></p>';
				return true; // End it here if there is an error with activation
			}
			else {
				printf( '<div id="message" class="updated"><p>%1$s</p></div>', __( 'Plugin ' . '<strong>' . $plugin['name'] . '</strong>' . ' activated.', $this->domain ) );
			}
			
		}
		
		return false;

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
	 * @global object $current_screen
	 * @return null Returns early if we're on the Install page
	 */
	public function notices() {

		global $current_screen;

		// Remove nag on the install page
		if ( $this->is_tgmpa_page() )
			return;

		$installed_plugins = get_plugins(); // Retrieve a list of all the plugins

		$this->populate_file_path();

		$message = array(); // Store the messages in an array to be outputted after plugins have looped through

		foreach ( $this->plugins as $plugin ) {

			if ( is_plugin_active( $plugin['file_path'] ) ) // If the plugin is active, no need to display nag
				continue;

				if ( ! isset( $installed_plugins[$plugin['file_path']] ) ) { // Not installed

					if ( current_user_can( 'install_plugins' ) ) {

						if ( $plugin['required'] ) {
							$message['notice_can_install_required'][] = $plugin['name'];
						}
						else { // This plugin is only recommended
							$message['notice_can_install_recommended'][] = $plugin['name'];
						}

					} else { // Need higher privileges to install the plugin
						$message['notice_cannot_install'][] = $plugin['name'];
					}

				} elseif ( is_plugin_inactive( $plugin['file_path'] ) ) { // Installed but not active

					if ( current_user_can( 'activate_plugins' ) ) {

						if ( $plugin['required'] ) {
							$message['notice_can_activate_required'][] = $plugin['name'];
						}
						else { // This plugin is only recommended
							$message['notice_can_activate_recommended'][] = $plugin['name'];
						}

					} else { // Need higher privileges to activate the plugin
						$message['notice_cannot_activate'][] = $plugin['name'];
					}

				}

		}

		if ( ! get_user_meta( get_current_user_id(), 'tgmpa_dismissed_notice', true ) ) {

			krsort( $message );

			if ( ! empty( $message ) ) {

				$rendered = ''; // Display all nag messages as strings

				foreach ( $message as $type => $plugin_groups ) { // Grab all plugin names

					$linked_plugin_groups = array();

					/** Loop through the plugin names to make the ones pulled from the .org repo linked */
					foreach ( $plugin_groups as $plugin_group_single_name ) {

						$source = $this->_get_plugin_data_from_name( $plugin_group_single_name, 'source' );
						if ( ! $source || preg_match( '|^http://wordpress.org/extend/plugins/|', $source ) ) {

							$url = add_query_arg( array(
								'tab'       => 'plugin-information',
								'plugin'    => $this->_get_plugin_data_from_name( $plugin_group_single_name ),
								'TB_iframe' => 'true',
								'width'     => '640',
								'height'    => '500',
							), admin_url( 'plugin-install.php' ) );

							$linked_plugin_groups[] .= '<a href="' . $url . '" class="thickbox" title="' . $plugin_group_single_name . '">' . $plugin_group_single_name . '</a>';

						}
						else {
							$linked_plugin_groups[] .= $plugin_group_single_name; // No hyperlink
						}

						if ( isset( $linked_plugin_groups ) && (array) $linked_plugin_groups )
							$plugin_groups = $linked_plugin_groups;

					}

					$last_plugin = array_pop( $plugin_groups ); // Pop off last name to prep for readability
					$imploded = empty( $plugin_groups ) ? '<em>' . $last_plugin . '</em>' : '<em>' . ( implode( ', ', $plugin_groups ) . '</em> and <em>' . $last_plugin . '</em>' );

					$rendered .= '<p>' . sprintf( $this->strings[$type], $imploded ) . '</p>'; // All messages now stored

				}

				/** Define all of the action links */
				$action_links = apply_filters( 'tgmpa_notice_action_links', array(
					'install'  => '<a href="' . add_query_arg( 'page', $this->menu, admin_url( 'themes.php' ) ) . '">' . __( 'Begin installing plugins', $this->domain ) . '</a>',
					'activate' => '<a href="' . admin_url( 'plugins.php' ) . '">' . __( 'Activate installed plugins', $this->domain ) . '</a>',
					'dismiss'  => '<a class="dismiss-notice" href="' . add_query_arg( 'tgmpa-dismiss', 'dismiss_admin_notices' ) . '" target="_parent">' . __( 'Dismiss this notice', $this->domain ) . '</a>' )
				);

				if ( $action_links )
					$rendered .= '<p>' . implode( ' | ', $action_links ) . '</p>';

				add_settings_error( 'tgmpa', 'tgmpa', $rendered, 'updated' );


			}

		}

		/** Admin options pages already output settings_errors, so this is to avoid duplication */
		if ( 'options-general' !== $current_screen->parent_base )
			settings_errors( 'tgmpa' );

	}

	/**
	 * Add dismissable admin notices.
	 *
	 * Appends a link to the admin nag messages. If clicked, the admin notice disappears and no longer is visible to users.
	 *
	 * @since 2.1.0
	 */
	public function dismiss() {

		if ( isset( $_GET[sanitize_key( 'tgmpa-dismiss' )] ) )
			update_user_meta( get_current_user_id(), 'tgmpa_dismissed_notice', 1 );

	}

	/**
	 * Add individual plugin to our collection of plugins.
	 *
	 * If the required keys are not set, the plugin is not added.
	 *
	 * @since 2.0.0
	 *
	 * @param array $plugin Array of plugin arguments.
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

		$keys = array( 'default_path', 'domain', 'notices', 'menu', 'strings' );

		foreach ( $keys as $key ) {

			if ( isset( $config[$key] ) ) {
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

		// Remove action links on the TGMPA install page
		if ( $this->is_tgmpa_page() )
			return false;

		return $install_actions;

	}

	/**
	 * Set file_path key for each installed plugin.
	 *
	 * @since 2.1.0
	 */
	public function populate_file_path() {

		// Add file_path key for all plugins
		foreach( $this->plugins as $plugin => $values )
			$this->plugins[$plugin]['file_path'] = $this->_get_plugin_basename_from_slug( $values['slug'] );

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

	/**
	 * Retrieve plugin data, given the plugin name.
	 *
	 * Loops through the registered plugins looking for $name. If it finds it,
	 * it returns the $data from that plugin. Otherwise, returns false.
	 *
	 * @since 2.1.0
	 *
	 * @param string $name Name of the plugin, as it was registered
	 * @param string $data Optional. Array key of plugin data to return. Default is slug
	 * @return string|boolean Plugin slug if found, false otherwise.
	 */
	protected function _get_plugin_data_from_name( $name, $data = 'slug' ) {

		foreach ( $this->plugins as $plugin => $values ) {
			if ( $name == $values['name'] && isset( $values[$data] ) )
				return $values[$data];
		}

		return false;

	}

	/**
	 * Determine if we're on the TGMPA Install page.
	 *
	 * We use $current_screen when it is available, and a slightly less ideal
	 * conditional when it isn't (like when displaying the plugin information
	 * thickbox).
	 *
	 * @since 2.1.0
	 *
	 * @global object $current_screen
	 * @return boolean True when on the TGMPA page , false otherwise.
	 */
	protected function is_tgmpa_page() {

		global $current_screen;

		if ( ! is_null( $current_screen ) && 'appearance_page_' . $this->menu == $current_screen->id )
			return true;

		if ( isset( $_GET['page'] ) && $this->menu === $_GET['page'] )
			return true;

		return false;

	}

	/**
	 * Delete dismissable nag option when theme is switched.
	 *
	 * This ensures that the user is again reminded via nag of required
	 * and/or recommended plugins if they re-activate the theme.
	 *
	 * @since 2.1.1
	 */
	public function update_dismiss() {

		delete_user_meta( get_current_user_id(), 'tgmpa_dismissed_notice' );

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




/**
 * WP_List_Table isn't always available. If it isn't available,
 * we load it here.
 *
 * @since 2.2.0
 */
if ( ! class_exists( 'WP_List_Table' ) )
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	
/**
 * List table class for handling plugins.
 *
 * Extends the WP_List_Table class to provide a future-compatible
 * way of listing out all required/recommended plugins.
 *
 * Gives users an interface similar to the Plugin Administration
 * area with similar (albeit stripped down) capabilities.
 *
 * This class also allows for the bulk install of plugins.
 *
 * @since 2.2.0
 *
 * @package TGM-Plugin-Activation
 * @author Thomas Griffin <thomas@thomasgriffinmedia.com>
 * @author Gary Jones <gamajo@gamajo.com>
 */
class TGMPA_List_Table extends WP_List_Table {
	
	/**
	 * References parent constructor and sets defaults for class.
	 *
	 * The constructor also grabs a copy of $instance from the TGMPA class
	 * and stores it in the global object $tgmpa.
	 *
	 * @since 2.2.0
	 *
	 * @global unknown $status
	 * @global string $page
	 * @global object $tgmpa
	 */
	public function __construct() {
	
		global $status, $page, $_tgmpa;
		
		$object = new ReflectionProperty( 'TGM_Plugin_Activation', 'instance' ); // Store TGMPA static $instance in an object
		
		$_tgmpa = $object->getValue(); // Get the value of the instance
		
		parent::__construct( array(
			'singular' 	=> 'plugin',
			'plural' 	=> 'plugins',
			'ajax' 		=> false
		) );
	
	}
	
	/**
	 * Gathers and renames all of our plugin information to be used by 
	 * WP_List_Table to create our table.
	 *
	 * @since 2.2.0
	 *
	 */
	protected function _gather_plugin_data() {
	
		global $_tgmpa;
		
		$table_data = array();
		
		$i = 0;
		
		$installed_plugins = get_plugins();
		
		$_tgmpa->admin_init();
		
		$_tgmpa->thickbox();
		
		foreach ( $_tgmpa->plugins as $plugin ) {
		
			if ( is_plugin_active( $plugin['file_path'] ) )
				continue; // No need to display plugins if they are installed and activated
		
			$table_data[$i]['sanitized_plugin'] = $plugin['name'];
			
			$table_data[$i]['slug'] = $this->_get_plugin_data_from_name( $plugin['name'] );
		
			$source = $this->_get_plugin_data_from_name( $plugin['name'], 'source' );
			if ( ! $source || preg_match( '|^http://wordpress.org/extend/plugins/|', $source ) ) {

				$url = add_query_arg( array(
					'tab'       => 'plugin-information',
					'plugin'    => $this->_get_plugin_data_from_name( $plugin['name'] ),
					'TB_iframe' => 'true',
					'width'     => '640',
					'height'    => '500',
				), admin_url( 'plugin-install.php' ) );

				$table_data[$i]['plugin'] = '<strong><a href="' . $url . '" class="thickbox" title="' . $plugin['name'] . '">' . $plugin['name'] . '</a></strong>';

			}
			else {
				$table_data[$i]['plugin'] = '<strong>' . $plugin['name'] . '</strong>'; // No hyperlink
			}

			if ( isset( $table_data[$i]['plugin'] ) && (array) $table_data[$i]['plugin'] )
				$plugin['name'] = $table_data[$i]['plugin'];
				
			$table_data[$i]['source'] = isset( $plugin['source'] ) ? __( 'Pre-Packaged', $_tgmpa->domain ) : __( 'Repository', $_tgmpa->domain );
			
			$table_data[$i]['type'] = $plugin['required'] ? __( 'Required', $_tgmpa->domain ) : __( 'Recommended', $_tgmpa->domain );
			
			if ( is_plugin_active( $plugin['file_path'] ) )
				$table_data[$i]['status'] = sprintf( '<span style="background: #ebffe8;">%1$s</span>', __( 'Installed / Activated', $_tgmpa->domain ) );
				
			if ( ! isset( $installed_plugins[$plugin['file_path']] ) )
				$table_data[$i]['status'] = sprintf( '<span style="background: #ffebe8;">%1$s</span>', __( 'Not Installed / Not Activated', $_tgmpa->domain ) );
			elseif ( is_plugin_inactive( $plugin['file_path'] ) )
				$table_data[$i]['status'] = sprintf( '<span style="background: #ffffe0;">%1$s</span>', __( 'Installed / Not Activated', $_tgmpa->domain ) );
				
			$table_data[$i]['file_path'] = $plugin['file_path'];
			
			$table_data[$i]['url'] = isset( $plugin['source'] ) ? $plugin['source'] : 'repo';
					
			$i++;
			
		}
			
		return $table_data;
		
	}
	
	/**
	 * Retrieve plugin data, given the plugin name. Taken from the
	 * TGM_Plugin_Activation class.
	 *
	 * Loops through the registered plugins looking for $name. If it finds it,
	 * it returns the $data from that plugin. Otherwise, returns false.
	 *
	 * @since 2.2.0
	 *
	 * @param string $name Name of the plugin, as it was registered
	 * @param string $data Optional. Array key of plugin data to return. Default is slug
	 * @return string|boolean Plugin slug if found, false otherwise.
	 */
	protected function _get_plugin_data_from_name( $name, $data = 'slug' ) {
	
		global $_tgmpa;

		foreach ( $_tgmpa->plugins as $plugin => $values ) {
			if ( $name == $values['name'] && isset( $values[$data] ) )
				return $values[$data];
		}

		return false;

	}
	
	/**
	 * Create default columns to display important plugin information
	 * like type, action and status.
	 *
	 * @since 2.2.0
	 *
	 * @param array $item
	 * @param string $column_name
	 */
	public function column_default( $item, $column_name ) {
	
		switch( $column_name ) {
		
			case 'source' :
			case 'type' :
			case 'status' :
				return $item[$column_name];
			
			default :
				return print_r( $item, true );
		
		}
	
	}
	
	/**
	 * Create default title column along with action links of 'Install'
	 * and 'Activate'.
	 *
	 * @since 2.2.0
	 *
	 * @param array $item
	 */
	public function column_plugin( $item ) {
	
		global $_tgmpa;
		
		$installed_plugins = get_plugins();
		
		if ( is_plugin_active( $item['file_path'] ) )
			$actions = array();
				
		if ( ! isset( $installed_plugins[$item['file_path']] ) )
			$actions = array(
				'install' => sprintf( '<a href="%1$s" title="Install %2$s">Install</a>', wp_nonce_url( add_query_arg( array( 'page' => $_tgmpa->menu, 'plugin' => $item['slug'], 'plugin_name' => $item['sanitized_plugin'], 'plugin_source' => $item['url'], 'tgmpa-install' => 'install-plugin' ), admin_url( 'themes.php' ) ), 'tgmpa-install' ), $item['sanitized_plugin'] )
			);
		elseif ( is_plugin_inactive( $item['file_path'] ) )
			$actions = array(
				'activate' => sprintf( '<a href="%1$s" title="Activate %2$s">Activate</a>', wp_nonce_url( add_query_arg( array( 'page' => $_tgmpa->menu, 'plugin' => $item['slug'], 'plugin_name' => $item['sanitized_plugin'], 'plugin_source' => $item['url'], 'tgmpa-activate' => 'activate-plugin' ), admin_url( 'themes.php' ) ), 'tgmpa-activate' ), $item['sanitized_plugin'] )
			);
			
		return sprintf( '%1$s %2$s', $item['plugin'], $this->row_actions( $actions ) );
	
	}
	
	/**
	 * Required for bulk installing.
	 *
	 * Adds a checkbox for each plugin.
	 *
	 * @since 2.2.0
	 *
	 * @param array $item
	 */
	public function column_cb( $item ) {
	
		return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" id="%3$s" />', $this->_args['singular'], $item['file_path'] . ',' . $item['url'] . ',' . $item['sanitized_plugin'], $item['sanitized_plugin'] );
	
	}
	
	/**
	 * Output all the column information within the table.
	 *
	 * @since 2.2.0
	 */
	public function get_columns() {
	
		global $_tgmpa;
	
		$columns = array(
			'cb' 		=> '<input type="checkbox" />',
			'plugin' 	=> __( 'Plugin', $_tgmpa->domain ),
			'source' 	=> __( 'Source', $_tgmpa->domain ),
			'type' 		=> __( 'Type', $_tgmpa->domain ),
			'status' 	=> __( 'Status', $_tgmpa->domain )
		);
		
		return $columns;
	
	}
	
	/**
	 * Defines all types of bulk actions for handling
	 * registered plugins.
	 *
	 * @since 2.2.0
	 */
	public function get_bulk_actions() {
	
		global $_tgmpa;
	
		$actions = array(
			'tgmpa-bulk-install' 	=> __( 'Install', $_tgmpa->domain ),
			'tgmpa-bulk-activate' 	=> __( 'Activate', $_tgmpa->domain ),
		);
		
		return $actions;
	
	}
	
	/**
	 * Processes bulk installation and activation actions.
	 *
	 * @since 2.2.0
	 */
	protected function _process_bulk_actions() {
	
		global $_tgmpa;
	
		if ( 'tgmpa-bulk-install' === $this->current_action() ) {
		
			check_admin_referer( 'bulk-' . $this->_args['plural'] );
			
			$plugins = isset( $_POST[sanitize_key( 'plugin' )] ) ? (array) $_POST[sanitize_key( 'plugin' )] : array();
			$plugins_to_install = array();
			
			/** Split plugin value into array with plugin file path, plugin source and plugin name */
			foreach ( $plugins as $i => $plugin )
				$plugins_to_install[] = explode( ',', $plugin );
				
			foreach ( $plugins_to_install as $i => $array ) {
			
				if ( preg_match( '|.php$|', $array[0] ) ) // Plugins that haven't been installed yet won't have the correct file path
					unset( $plugins_to_install[$i] );
					
			}
			
			/** If our check removes all plugins from the array, do nothing */
			if ( empty( $plugins_to_install ) )
				return;
				
			$method = ''; // Leave blank so WP_Filesystem can populate it as necessary

			$url = wp_nonce_url( 'themes.php?page=' . $_tgmpa->menu, 'tgmpa-install' ); // Make sure we are coming from the right page
			if ( false === ( $creds = request_filesystem_credentials( $url, $method, false, false ) ) )
				return true;

			if ( ! WP_Filesystem( $creds ) ) {

				request_filesystem_credentials( $url, $method, true, false ); // Setup WP_Filesystem
				return true;

			}
			
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php'; // Need for plugins_api
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php'; // Need for upgrade classes
			
			$api = array(); // Store plugins_api object information in an array
			$source = array();
			
			foreach ( $plugins_to_install as $plugin )
				$api[] = plugins_api( 'plugin_information', array( 'slug' => $plugin[0], 'fields' => array( 'sections' => false ) ) );
				
			if ( is_wp_error( $api ) )
				wp_die( $_tgmpa->strings['oops'] . var_dump( $api ) );
			
			foreach ( $api as $object )		
				$source[] = isset( $object->download_link ) ? $object->download_link : '';
				
			$i = 0;
				
			foreach ( $plugins_to_install as $plugin ) {
			
				// Prep variables for Plugin_Installer_Skin class
				$title = sprintf( $_tgmpa->strings['installing'], $plugin[2] );
				$nonce = 'install-plugin_' . $plugin[0];
				$url = add_query_arg( array( 'action' => 'install-plugin', 'plugin' => $plugin[0] ), 'update.php' );
				if ( isset( $_GET['from'] ) )
					$url .= add_query_arg( 'from', urlencode( stripslashes( $_GET['from'] ) ), $url );
				
				if ( isset( $plugin[1] ) && 'repo' == $plugin[1] && isset( $source[$i] ) )
					$plugin[1] = $source[$i];
					
				/** Set type, based on whether the source starts with http:// or https:// */
				$type = preg_match('|^http(s)?://|', $plugin[1] ) ? 'web' : 'upload';

				/** Prefix a default path to pre-packaged plugins */
				$install_package = ( 'upload' == $type ) ? $_tgmpa->default_path . $plugin[1] : $plugin[1];
				
				$upgrader = new Plugin_Upgrader( new Plugin_Installer_Skin( compact( 'title', 'url', 'nonce', 'plugin' ), 'api' ) ); // Create a new instance of Plugin_Upgrader

				$upgrader->install( $install_package ); // Perform the action and install the plugin from the $source urldecode()

				$plugin_activate = $upgrader->plugin_info(); // Grab the plugin info from the Plugin_Upgrader method

				wp_cache_flush(); // Flush the cache to remove plugin header errors

				$activate = activate_plugin( $plugin_activate ); // Activate the plugin

				$_tgmpa->populate_file_path(); // Re-populate the file path now that the plugin has been installed and activated
				
				$i++;

				if ( is_wp_error( $activate ) ) {
					echo '<div id="message" class="error"><p>' . $activate->get_error_message() . '</p></div>';
					echo '<p><a href="' . add_query_arg( 'page', $_tgmpa->menu, admin_url( 'themes.php' ) ) . '" title="' . esc_attr( $_tgmpa->strings['return'] ) . '" target="_parent">' . __( 'Return to Required Plugins Installer', $_tgmpa->domain ) . '</a></p>';
					return true; // End it here if there is an error with automatic activation
				}
				else {
					echo '<p>' . $_tgmpa->strings['plugin_activated'] . '</p>';
				}
				
			}
		
		}
			
		if ( 'tgmpa-bulk-activate' === $this->current_action() ) {
		
			check_admin_referer( 'bulk-' . $this->_args['plural'] );
			
			$plugins = isset( $_POST[sanitize_key( 'plugin' )] ) ? (array) $_POST[sanitize_key( 'plugin' )] : array();
			$plugins_to_activate = array();
			
			/** Split plugin value into array with plugin file path, plugin source and plugin name */
			foreach ( $plugins as $i => $plugin )
				$plugins_to_activate[] = explode( ',', $plugin );
				
			foreach ( $plugins_to_activate as $i => $array ) {
			
				if ( ! preg_match( '|.php$|', $array[0] ) ) // Plugins that haven't been installed yet won't have the correct file path
					unset( $plugins_to_activate[$i] );
					
			}
			
			if ( empty( $plugins_to_activate ) )
				return;
				
			$plugins = array();
			$plugin_names = array();
				
			foreach ( $plugins_to_activate as $plugin_string ) {
				$plugins[] = $plugin_string[0];
				$plugin_names[] = $plugin_string[2];
			}
			
			$last_plugin = array_pop( $plugin_names ); // Pop off last name to prep for readability
			$imploded = empty( $plugin_names ) ? '<strong>' . $last_plugin . '</strong>' : '<strong>' . ( implode( ', ', $plugin_names ) . '</strong> and <strong>' . $last_plugin . '</strong>.' );
				
			/** Now we are good to go - let's start activating plugins */
			$activate = activate_plugins( $plugins );
			
			if ( is_wp_error( $activate ) )
				echo '<div id="message" class="error"><p>' . $activate->get_error_message() . '</p></div>';
			else
				printf( '<div id="message" class="updated"><p>%1$s %2$s</p></div>', __( 'The following plugins were successfully activated:', $_tgmpa->domain ), $imploded );
 			
 			/** Update recently activated plugins option */
			$recent = (array) get_option( 'recently_activated' );
			
			foreach ( $plugins as $plugin => $time )
				if ( isset( $recent[$plugin] ) )
					unset( $recent[$plugin] );

			update_option( 'recently_activated', $recent );
		
		}
	
	}
	
	/**
	 * Prepares all of our information to be outputted into a usable table.
	 *
	 * @since 2.2.0
	 */
	public function prepare_items() {
	
		$per_page = 100; // Set it high so we shouldn't have to worry about pagination
	
		$columns = $this->get_columns();
		
		$hidden = array(); // No columns to hide, but we must set as an array
		
		$sortable = array(); // No reason to make sortable columns
		
		$this->_column_headers = array( $columns, $hidden, $sortable );
		
		$this->_process_bulk_actions();
		
		$this->items = $this->_gather_plugin_data(); // Grab all of our plugin information
	
	}

}