<?php
/**
 * Plugin installation and activation for WordPress themes.
 *
 * @package   TGM-Plugin-Activation
 * @version   2.1.1
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
			add_action( 'admin_print_styles', array( &$this, 'styles' ) );
			add_action( 'admin_head', array( &$this, 'dismiss' ) );
			add_filter( 'install_plugin_complete_actions', array( &$this, 'actions' ) );

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

		if ( isset( $_REQUEST['tab'] ) && 'plugin_information' == $_REQUEST['tab'] ) {

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

					if ( $plugin['required'] )
						echo '<div class="instructions"><p>' . sprintf( $this->strings['instructions_install'], '<strong>' . $plugin['name'] . '</strong>' ) . '</p>'; // Leave <div> tag open, close after the form has been printed
					else // This plugin is only recommended
						echo '<div class="instructions"><p>' . sprintf( $this->strings['instructions_install_recommended'], '<strong>' . $plugin['name'] . '</strong>' ) . '</p>'; // Leave <div> tag open, close after the form has been printed

				} elseif ( is_plugin_inactive( $plugin['file_path'] ) ) { // The plugin is installed but not active

					echo '<div class="instructions"><p>' . sprintf( $this->strings['instructions_activate'], '<strong>' . $plugin['name'] . '</strong>', admin_url( 'plugins.php' ) ) . '</p></div>';
					continue; // No need to display a form because it is already installed, just needs to be activated

				}
				?>
				<form action="" method="post">
					<?php
					wp_nonce_field( 'tgmpa' );
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

		check_admin_referer( 'tgmpa' ); // Security check

		foreach ( $this->plugins as $plugin ) { // Iterate and perform the action for each plugin in the array

			$fields = array( sanitize_key( $plugin['name'] ) );
			$method = ''; // Leave blank so WP_Filesystem can populate it as necessary

			if ( isset( $_POST[sanitize_key( $plugin['name'] )] ) ) { // Don't do anything if the form has not been submitted

				$url = wp_nonce_url( 'themes.php?page=' . $this->menu, 'tgmpa' ); // Make sure we are coming from the right page
				if ( false === ( $creds = request_filesystem_credentials( $url, $method, false, false, $fields ) ) )
					return true;

				if ( ! WP_Filesystem( $creds ) ) {

					request_filesystem_credentials( $url, $method, true, false, $fields ); // Setup WP_Filesystem
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
								'page'      => $this->menu,
								'tab'       => 'plugin_information',
								'plugin'    => $this->_get_plugin_data_from_name( $plugin_group_single_name ),
								'TB_iframe' => 'true',
								'width'     => '640',
								'height'    => '500',
							), admin_url( 'themes.php' ) );

							$linked_plugin_groups[] .= '<a href="' . $url . '" class="thickbox" title="' . $plugin_group_single_name . '">' . $plugin_group_single_name . '</a>';

						}
						else {
							$linked_plugin_groups[] .= $plugin_group_single_name; // No hyperlink
						}

						if ( isset( $linked_plugin_groups) && (array) $linked_plugin_groups )
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
	 * Enqueue a style sheet for this admin page.
	 *
	 * @since 1.1.0
	 *
	 * @global $current_screen
	 */
	public function styles() {

		// Only load the CSS file on the Install page
		if ( $this->is_tgmpa_page() )
			echo '<style type="text/css">' .
				'.tgmpa .instructions {
					-moz-border-radius: 3px;
					-webkit-border-radius: 3px;
					background: #f5f5f5;
					border: 1px solid #d5d5d5;
					border-radius: 3px;
					margin: 15px 0;
					width: 700px;
				}

				.tgmpa .instructions p {
					border-top: 1px solid #fff;
					margin: 0;
					padding: 1em;
				}

				.tgmpa p.submit {
					border-top: 0 none;
					padding-top: 0;
				}' .
			'</style>';

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