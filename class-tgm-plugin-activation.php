<?php
/**
 * Plugin installation and activation for WordPress themes.
 *
 * Please note that this is a drop-in library for a theme or plugin.
 * The authors of this library (Thomas and Gary) are NOT responsible
 * for the support of your plugin or theme. Please contact the plugin
 * or theme author for support.
 *
 * @package   TGM-Plugin-Activation
 * @version   2.5.0-alpha
 * @link      http://tgmpluginactivation.com/
 * @author    Thomas Griffin
 * @author    Gary Jones
 * @copyright Copyright (c) 2011, Thomas Griffin
 * @license   GPL-2.0+
 */

/*
	Copyright 2011 Thomas Griffin (thomasgriffinmedia.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! class_exists( 'TGM_Plugin_Activation' ) ) {
	/**
	 * Automatic plugin installation and activation library.
	 *
	 * Creates a way to automatically install and activate plugins from within themes.
	 * The plugins can be either pre-packaged, downloaded from the WordPress
	 * Plugin Repository or downloaded from a private repository.
	 *
	 * @since 1.0.0
	 *
	 * @package TGM-Plugin-Activation
	 * @author  Thomas Griffin
	 * @author  Gary Jones
	 */
	class TGM_Plugin_Activation {

		/**
		 * Holds a copy of itself, so it can be referenced by the class name.
		 *
		 * @since 1.0.0
		 *
		 * @var TGM_Plugin_Activation
		 */
		public static $instance;

		/**
		 * Holds arrays of plugin details.
		 *
		 * @since 1.0.0
		 *
		 * @var array
		 */
		public $plugins = array();

		/**
		 * Name of the unique ID to hash notices.
		 *
		 * @since 2.4.0
		 *
		 * @var string
		 */
		public $id = 'tgmpa';

		/**
		 * Name of the query-string argument for the admin page.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		public $menu = 'tgmpa-install-plugins';

		/**
		 * Parent menu file slug.
		 *
		 * @since 2.5.0
		 *
		 * @var string
		 */
		public $parent_slug = 'themes.php';

		/**
		 * Capability needed to view the plugin installation menu item.
		 *
		 * @since 2.5.0
		 *
		 * @var string
		 */
		public $capability = 'edit_theme_options';

		/**
		 * Default absolute path to folder containing pre-packaged plugin zip files.
		 *
		 * @since 2.0.0
		 *
		 * @var string Absolute path prefix to packaged zip file location. Default is empty string.
		 */
		public $default_path = '';

		/**
		 * Flag to show admin notices or not.
		 *
		 * @since 2.1.0
		 *
		 * @var boolean
		 */
		public $has_notices = true;

		/**
		 * Flag to determine if the user can dismiss the notice nag.
		 *
		 * @since 2.4.0
		 *
		 * @var boolean
		 */
		public $dismissable = true;

		/**
		 * Message to be output above nag notice if dismissable is false.
		 *
		 * @since 2.4.0
		 *
		 * @var string
		 */
		public $dismiss_msg = '';

		/**
		 * Flag to set automatic activation of plugins. Off by default.
		 *
		 * @since 2.2.0
		 *
		 * @var boolean
		 */
		public $is_automatic = false;

		/**
		 * Optional message to display before the plugins table.
		 *
		 * @since 2.2.0
		 *
		 * @var string Message filtered by wp_kses_post(). Default is empty string.
		 */
		public $message = '';

		/**
		 * Holds configurable array of strings.
		 *
		 * Default values are added in the constructor.
		 *
		 * @since 2.0.0
		 *
		 * @var array
		 */
		public $strings = array();

		/**
		 * Holds the version of WordPress.
		 *
		 * @since 2.4.0
		 *
		 * @var int
		 */
		public $wp_version;

		/**
		 * Holds the hook name for the admin page
		 *
		 * @since 2.5.0
		 *
		 * @var string
		 */
		public $page_hook;

		/**
		 * Adds a reference of this object to $instance, populates default strings,
		 * does the tgmpa_init action hook, and hooks in the interactions to init.
		 *
		 * @since 1.0.0
		 *
		 * @see TGM_Plugin_Activation::init()
		 */
		protected function __construct() {

			// Set the current WordPress version.
			$this->wp_version = $GLOBALS['wp_version'];

			// Announce that the class is ready, and pass the object (for advanced use).
			do_action_ref_array( 'tgmpa_init', array( $this ) );

			// When the rest of WP has loaded, kick-start the rest of the class.
			add_action( 'init', array( $this, 'init' ) );

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
			
			if ( apply_filters( 'tgmpa_load', ! is_admin() ) ) {
				return;
			}

			// Load class strings.
			$this->strings = array(
				'page_title'                      => __( 'Install Required Plugins', 'tgmpa' ),
				'menu_title'                      => __( 'Install Plugins', 'tgmpa' ),
				'installing'                      => __( 'Installing Plugin: %s', 'tgmpa' ),
				'oops'                            => __( 'Something went wrong.', 'tgmpa' ),
				'notice_can_install_required'     => _n_noop( 'This theme requires the following plugin: %1$s.', 'This theme requires the following plugins: %1$s.', 'tgmpa' ),
				'notice_can_install_recommended'  => _n_noop( 'This theme recommends the following plugin: %1$s.', 'This theme recommends the following plugins: %1$s.', 'tgmpa' ),
				'notice_cannot_install'           => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.', 'tgmpa' ),
				'notice_can_activate_required'    => _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.', 'tgmpa' ),
				'notice_can_activate_recommended' => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.', 'tgmpa' ),
				'notice_cannot_activate'          => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.', 'tgmpa' ),
				'notice_ask_to_update'            => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.', 'tgmpa' ),
				'notice_cannot_update'            => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.', 'tgmpa' ),
				'install_link'                    => _n_noop( 'Begin installing plugin', 'Begin installing plugins', 'tgmpa' ),
				'activate_link'                   => _n_noop( 'Begin activating plugin', 'Begin activating plugins', 'tgmpa' ),
				'return'                          => __( 'Return to Required Plugins Installer', 'tgmpa' ),
				'dashboard'                       => __( 'Return to the dashboard', 'tgmpa' ),
				'plugin_activated'                => __( 'Plugin activated successfully.', 'tgmpa' ),
				'activated_successfully'          => __( 'The following plugin was activated successfully:', 'tgmpa' ),
				'complete'                        => __( 'All plugins installed and activated successfully. %1$s', 'tgmpa' ),
				'dismiss'                         => __( 'Dismiss this notice', 'tgmpa' ),
			);

			do_action( 'tgmpa_register' );
			// After this point, the plugins should be registered and the configuration set.

			// Proceed only if we have plugins to handle.
			if ( ! is_array( $this->plugins ) || empty( $this->plugins ) ) {
				return;
			}

			$sorted = array();

			foreach ( $this->plugins as $plugin ) {
				$sorted[] = $plugin['name'];
			}

			array_multisort( $sorted, SORT_ASC, $this->plugins );

			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_head', array( $this, 'dismiss' ) );
			add_filter( 'install_plugin_complete_actions', array( $this, 'actions' ) );
			add_action( 'switch_theme', array( $this, 'flush_plugins_cache' ) );

			if ( $this->has_notices ) {
				add_action( 'admin_notices', array( $this, 'notices' ) );
				add_action( 'admin_init', array( $this, 'admin_init' ), 1 );
				add_action( 'admin_enqueue_scripts', array( $this, 'thickbox' ) );
				add_action( 'switch_theme', array( $this, 'update_dismiss' ) );
			}

			// Setup the force activation hook.
			foreach ( $this->plugins as $plugin ) {
				if ( isset( $plugin['force_activation'] ) && true === $plugin['force_activation'] ) {
					add_action( 'admin_init', array( $this, 'force_activation' ) );
					break;
				}
			}

			// Setup the force deactivation hook.
			foreach ( $this->plugins as $plugin ) {
				if ( isset( $plugin['force_deactivation'] ) && true === $plugin['force_deactivation'] ) {
					add_action( 'switch_theme', array( $this, 'force_deactivation' ) );
					break;
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
		 * WP does not make it easy to show the plugin information in the thickbox -
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

			if ( ! $this->is_tgmpa_page() ) {
				return;
			}

			if ( isset( $_REQUEST['tab'] ) && 'plugin-information' === $_REQUEST['tab'] ) {
				require_once ABSPATH . 'wp-admin/includes/plugin-install.php'; // Need for install_plugin_information().

				wp_enqueue_style( 'plugin-install' );

				global $tab, $body_id;
				$body_id = 'plugin-information';
				$tab     = 'plugin-information';

				install_plugin_information();

				exit;
			}

		}

		/**
		 * Enqueue thickbox scripts/styles for plugin info.
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

			if ( ! get_user_meta( get_current_user_id(), 'tgmpa_dismissed_notice_' . $this->id, true ) ) {
				add_thickbox();
			}

		}

		/**
		 * Adds submenu page if there are plugin actions to take.
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
			if ( ! current_user_can( 'install_plugins' ) ) {
				return;
			}

			$this->populate_file_path();

			foreach ( $this->plugins as $plugin ) {
				if ( ! is_plugin_active( $plugin['file_path'] ) ) {

					$args = apply_filters(
						'tgmpa_admin_menu_args',
						array(
							'parent_slug' => $this->parent_slug,                     // Parent Menu slug.
							'page_title'  => $this->strings['page_title'],           // Page title.
							'menu_title'  => $this->strings['menu_title'],           // Menu title.
							'capability'  => $this->capability,                      // Capability.
							'menu_slug'   => $this->menu,                            // Menu slug.
							'function'    => array( $this, 'install_plugins_page' ), // Callback.
						)
					);

					$this->add_admin_menu( $args );

					break;
				}
			}

		}

		/**
		 * Add the menu item.
		 *
		 * @since 2.5.0
		 *
		 * @param array $args Menu item configuration.
		 */
		protected function add_admin_menu( array $args ) {
			if ( apply_filters( 'tgmpa_admin_menu_use_add_theme_page', true ) ) {
				$this->page_hook = call_user_func( 'add_theme_page', $args['page_title'], $args['menu_title'], $args['capability'], $args['menu_slug'], $args['function'] );
			}
			else {
				$this->page_hook = call_user_func( 'add_submenu_page', $args['parent_slug'], $args['page_title'], $args['menu_title'], $args['capability'], $args['menu_slug'], $args['function'] );
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
		 * @return null Aborts early if we're processing a plugin installation action
		 */
		public function install_plugins_page() {

			// Store new instance of plugin table in object.
			$plugin_table = new TGMPA_List_Table;

			// Return early if processing a plugin installation action.
			if ( ( 'tgmpa-bulk-install' === $plugin_table->current_action() && $plugin_table->process_bulk_actions() ) || $this->do_plugin_install() ) {
				return;
			}

			?>
			<div class="tgmpa wrap">
				<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
				<?php $plugin_table->prepare_items(); ?>

				<?php
				if ( isset( $this->message ) && is_string( $this->message ) && ! empty( $this->message ) ) {
					echo wp_kses_post( $this->message );
				}
				?>

				<form id="tgmpa-plugins" action="" method="post">
					<input type="hidden" name="tgmpa-page" value="<?php echo esc_attr( $this->menu ); ?>" />
					<?php $plugin_table->display(); ?>
				</form>

			</div>
			<?php

		}

		/**
		 * Installs a plugin or activates a plugin depending on the hover
		 * link clicked by the user.
		 *
		 * Checks the $_GET variable to see which actions have been
		 * passed and responds with the appropriate method.
		 *
		 * Uses WP_Filesystem to process and handle the plugin installation
		 * method.
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

			// All plugin information will be stored in an array for processing.
			$plugin = array();

			// Checks for actions from hover links to process the installation.
			if ( isset( $_GET['plugin'] ) && ( isset( $_GET['tgmpa-install'] ) && 'install-plugin' === $_GET['tgmpa-install'] ) ) {
				check_admin_referer( 'tgmpa-install' );

				$plugin['name']   = $_GET['plugin_name'];
				$plugin['slug']   = sanitize_title( $_GET['plugin'] );
				$plugin['source'] = $_GET['plugin_source'];

				// Pass all necessary information via URL if WP_Filesystem is needed.
				$url = wp_nonce_url(
					add_query_arg(
						array(
							'page'          => urlencode( $this->menu ),
							'plugin'        => urlencode( $plugin['slug'] ),
							'plugin_name'   => urlencode( $plugin['name'] ),
							'plugin_source' => urlencode( $plugin['source'] ),
							'tgmpa-install' => 'install-plugin',
						),
						self_admin_url( $this->parent_slug )
					),
					'tgmpa-install'
				);

				$method = ''; // Leave blank so WP_Filesystem can populate it as necessary.
				$fields = array( 'tgmpa-install' ); // Extra fields to pass to WP_Filesystem.

				if ( false === ( $creds = request_filesystem_credentials( esc_url_raw( $url ), $method, false, false, $fields ) ) ) {
					return true;
				}

				if ( ! WP_Filesystem( $creds ) ) {
					request_filesystem_credentials( esc_url_raw( $url ), $method, true, false, $fields ); // Setup WP_Filesystem.
					return true;
				}

				require_once ABSPATH . 'wp-admin/includes/plugin-install.php'; // Need for plugins_api.
				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php'; // Need for upgrade classes.

				// Set plugin source to WordPress API link if available.
				if ( isset( $plugin['source'] ) && 'repo' === $plugin['source'] ) {
					$api = plugins_api( 'plugin_information', array( 'slug' => $plugin['slug'], 'fields' => array( 'sections' => false ) ) );

					if ( is_wp_error( $api ) ) {
						if ( true === WP_DEBUG ) {
							wp_die( esc_html( $this->strings['oops'] ) . var_dump( $api ) ); // wpcs: xss ok
						}
						else {
							wp_die( esc_html( $this->strings['oops'] ) );
						}
					}

					if ( isset( $api->download_link ) ) {
						$plugin['source'] = $api->download_link;
					}
				}

				// Set type, based on whether the source starts with http:// or https://.
				$type = preg_match( '|^http(s)?://|', $plugin['source'] ) ? 'web' : 'upload';

				// Prep variables for Plugin_Installer_Skin class.
				$title = sprintf( $this->strings['installing'], $plugin['name'] );
				$url   = add_query_arg( array( 'action' => 'install-plugin', 'plugin' => urlencode( $plugin['slug'] ) ), 'update.php' );
				$url   = esc_url_raw( $url );

				$nonce = 'install-plugin_' . $plugin['slug'];

				// Prefix a default path to pre-packaged plugins.
				$source = ( 'upload' === $type ) ? $this->default_path . $plugin['source'] : $plugin['source'];

				// Create a new instance of Plugin_Upgrader.
				$upgrader = new Plugin_Upgrader( new Plugin_Installer_Skin( compact( 'type', 'title', 'url', 'nonce', 'plugin', 'api' ) ) );

				// Perform the action and install the plugin from the $source urldecode().
				add_filter( 'upgrader_source_selection', array( $this, 'maybe_adjust_source_dir' ), 1, 3 );
				$upgrader->install( $source );
				remove_filter( 'upgrader_source_selection', array( $this, 'maybe_adjust_source_dir' ), 1, 3 );

				// Flush plugins cache so we can make sure that the installed plugins list is always up to date.
				$this->flush_plugins_cache();

				// Only activate plugins if the config option is set to true.
				if ( $this->is_automatic ) {
					$plugin_activate = $upgrader->plugin_info(); // Grab the plugin info from the Plugin_Upgrader method.
					$activate        = activate_plugin( $plugin_activate ); // Activate the plugin.
					$this->populate_file_path(); // Re-populate the file path now that the plugin has been installed and activated.

					if ( is_wp_error( $activate ) ) {
						echo '<div id="message" class="error"><p>', wp_kses_post( $activate->get_error_message() ), '</p></div>',
							'<p><a href="', esc_url( add_query_arg( 'page', urlencode( $this->menu ), self_admin_url( 'themes.php' ) ) ), '" target="_parent">', esc_html( $this->strings['return'] ), '</a></p>';
						return true; // End it here if there is an error with automatic activation
					}
					else {
						echo '<p>', esc_html( $this->strings['plugin_activated'] ), '</p>';
					}
				}

				// Display message based on if all plugins are now active or not.
				$complete = true;
				foreach ( $this->plugins as $plugin ) {
					if ( ! is_plugin_active( $plugin['file_path'] ) ) {
						echo '<p><a href="', esc_url( add_query_arg( 'page', urlencode( $this->menu ), self_admin_url( $this->parent_slug ) ) ), '" target="_parent">', esc_html( $this->strings['return'] ), '</a></p>';
						$complete = false;
						break;
					}
				}

				// All plugins are active, so we display the complete string and hide the plugin menu.
				if ( true === $complete ) {
					echo '<p>', sprintf( esc_html( $this->strings['complete'] ), '<a href="' . esc_url( self_admin_url() ) . '">' . esc_html__( 'Return to the Dashboard', 'tgmpa' ) . '</a>' ), '</p>';
					echo '<style type="text/css">#adminmenu .wp-submenu li.current { display: none !important; }</style>';
				}

				return true;
			}
			// Checks for actions from hover links to process the activation.
			elseif ( isset( $_GET['plugin'] ) && ( isset( $_GET['tgmpa-activate'] ) && 'activate-plugin' === $_GET['tgmpa-activate'] ) ) {
				check_admin_referer( 'tgmpa-activate', 'tgmpa-activate-nonce' );

				// Populate $plugin array with necessary information.
				$plugin['name']   = $_GET['plugin_name'];
				$plugin['slug']   = sanitize_title( $_GET['plugin'] );
				$plugin['source'] = $_GET['plugin_source'];

				$plugin_data        = get_plugins( '/' . $plugin['slug'] ); // Retrieve all plugins.
				$plugin_file        = array_keys( $plugin_data ); // Retrieve all plugin files from installed plugins.
				$plugin_to_activate = $plugin['slug'] . '/' . $plugin_file[0]; // Match plugin slug with appropriate plugin file.
				$activate           = activate_plugin( $plugin_to_activate ); // Activate the plugin.

				if ( is_wp_error( $activate ) ) {
					echo '<div id="message" class="error"><p>', wp_kses_post( $activate->get_error_message() ), '</p></div>';
					echo '<p><a href="', esc_url( add_query_arg( 'page', urlencode( $this->menu ), self_admin_url( $this->parent_slug ) ) ), '" target="_parent">', esc_html( $this->strings['return'] ), '</a></p>';
					return true; // End it here if there is an error with activation.
				}
				else {
					// Make sure message doesn't display again if bulk activation is performed immediately after a single activation.
					if ( ! isset( $_POST['action'] ) ) {
						echo '<div id="message" class="updated"><p>', esc_html( $this->strings['activated_successfully'] ), ' <strong>', esc_html( $plugin['name'] ), '.</strong></p></div>';
					}
				}
			}

			return false;

		}

		/**
		 * Adjust the plugin directory name if necessary.
		 *
		 * The final destination directory of a plugin is based on the subdirectory name found in the
		 * (un)zipped source. In some cases - most notably GitHub repository plugin downloads -, this
		 * subdirectory name is not the same as the expected slug and the plugin will not be recognized
		 * as installed. This is fixed by adjusting the temporary unzipped source subdirectory name to
		 * the expected plugin slug.
		 *
		 * @param string       $source        Path to upgrade/zip-file-name.tmp/subdirectory/
		 * @param string       $remote_source Path to upgrade/zip-file-name.tmp
		 * @param \WP_Upgrader $upgrader      Instance of the upgrader which installs the plugin
		 *
		 * @return string $source
		 */
		public function maybe_adjust_source_dir( $source, $remote_source, $upgrader ) {

			if ( ! $this->is_tgmpa_page() ) {
				return $source;
			}

			// Check for single file plugins
			$source_files = array_keys( $GLOBALS['wp_filesystem']->dirlist( $remote_source ) );
			if ( 1 === count( $source_files ) && false === $GLOBALS['wp_filesystem']->is_dir( $source ) ) {

				return $source;
			}

			// Multi-file plugin, let's see if the directory is correctly named
			$desired_slug = '';

			// Figure out what the slug is supposed to be
			if ( false === $upgrader->bulk ) {
				$desired_slug = $upgrader->skin->options['plugin']['slug'];
			}
			else {
				// Bulk installer contains less info, so fall back on the info registered here.
				foreach ( $this->plugins as $plugin ) {
					if ( $plugin['name'] === $upgrader->skin->plugin_names[ $upgrader->skin->i ] ) {
						$desired_slug = $plugin['slug'];
						break;
					}
				}
			}

			if ( '' !== $desired_slug ) {
				$subdir_name = untrailingslashit( str_replace( trailingslashit( $remote_source ), '', $source ) );

				if ( ! empty( $subdir_name ) && $subdir_name !== $desired_slug ) {
					$from = untrailingslashit( $source );
					$to   = trailingslashit( $remote_source ) . $desired_slug;

					if ( true === $GLOBALS['wp_filesystem']->move( $from, $to ) ) {
						return trailingslashit( $to );
					}
					else {
						return new WP_Error( 'rename_failed', esc_html__( 'The remote plugin package is does not contain a folder with the desired slug and renaming did not work.', 'tgmpa' ) . ' ' . esc_html__( 'Please contact the plugin provider and ask them to package their plugin according to the WordPress guidelines.', 'tgmpa' ), array( 'found' => $subdir_name, 'expected' => $desired_slug ) );
					}
				}
				elseif ( empty( $subdir_name ) ) {
					return new WP_Error( 'packaged_wrong', esc_html__( 'The remote plugin package consists of more than one file, but the files are not packaged in a folder.', 'tgmpa' ) . ' ' . esc_html__( 'Please contact the plugin provider and ask them to package their plugin according to the WordPress guidelines.', 'tgmpa' ), array( 'found' => $subdir_name, 'expected' => $desired_slug ) );
				}
			}

			return $source;
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
		 * @return null Returns early if we're on the Install page.
		 */
		public function notices() {

			// Remove nag on the install page.
			if ( $this->is_tgmpa_page() ) {
				return;
			}

			// Return early if the nag message has been dismissed.
			if ( get_user_meta( get_current_user_id(), 'tgmpa_dismissed_notice_' . $this->id, true ) ) {
				return;
			}

			$installed_plugins = get_plugins(); // Retrieve a list of all the plugins
			$this->populate_file_path();

			$message             = array(); // Store the messages in an array to be outputted after plugins have looped through.
			$install_link        = false;   // Set to false, change to true in loop if conditions exist, used for action link 'install'.
			$install_link_count  = 0;       // Used to determine plurality of install action link text.
			$activate_link       = false;   // Set to false, change to true in loop if conditions exist, used for action link 'activate'.
			$activate_link_count = 0;       // Used to determine plurality of activate action link text.

			foreach ( $this->plugins as $plugin ) {
				// If the plugin is installed and active, check for minimum version argument before moving forward.
				if ( is_plugin_active( $plugin['file_path'] ) || ( isset( $plugin['is_callable'] ) && is_callable( $plugin['is_callable'] ) ) ) {
					// A minimum version has been specified.
					if ( isset( $plugin['version'] ) ) {
						if ( isset( $installed_plugins[ $plugin['file_path'] ]['Version'] ) ) {
							// If the current version is less than the minimum required version, we display a message.
							if ( version_compare( $installed_plugins[ $plugin['file_path'] ]['Version'], $plugin['version'], '<' ) ) {
								if ( current_user_can( 'install_plugins' ) ) {
									$message['notice_ask_to_update'][] = $plugin['name'];
								}
								else {
									$message['notice_cannot_update'][] = $plugin['name'];
								}
							}
						}
						// Can't find the plugin, so iterate to the next condition.
						else {
							continue;
						}
					}
					// No minimum version specified, so iterate over the plugin.
					else {
						continue;
					}
				}

				// Not installed.
				if ( ! isset( $installed_plugins[ $plugin['file_path'] ] ) ) {
					$install_link = true; // We need to display the 'install' action link.
					$install_link_count++; // Increment the install link count.
					if ( current_user_can( 'install_plugins' ) ) {
						if ( isset( $plugin['required'] ) && $plugin['required'] ) {
							$message['notice_can_install_required'][] = $plugin['name'];
						}
						// This plugin is only recommended.
						else {
							$message['notice_can_install_recommended'][] = $plugin['name'];
						}
					}
					// Need higher privileges to install the plugin.
					else {
						$message['notice_cannot_install'][] = $plugin['name'];
					}
				}
				// Installed but not active.
				elseif ( is_plugin_inactive( $plugin['file_path'] ) ) {
					$activate_link = true; // We need to display the 'activate' action link.
					$activate_link_count++; // Increment the activate link count.
					if ( current_user_can( 'activate_plugins' ) ) {
						if ( isset( $plugin['required'] ) && $plugin['required'] ) {
							$message['notice_can_activate_required'][] = $plugin['name'];
						}
						// This plugin is only recommended.
						else {
							$message['notice_can_activate_recommended'][] = $plugin['name'];
						}
					}
					// Need higher privileges to activate the plugin.
					else {
						$message['notice_cannot_activate'][] = $plugin['name'];
					}
				}
			}

			// If we have notices to display, we move forward.
			if ( ! empty( $message ) ) {
				krsort( $message ); // Sort messages.
				$rendered = ''; // Display all nag messages as strings.

				// If dismissable is false and a message is set, output it now.
				if ( ! $this->dismissable && ! empty( $this->dismiss_msg ) ) {
					$rendered .= '<p><strong>' . wp_kses_post( $this->dismiss_msg ) . '</strong></p>';
				}

				// Grab all plugin names.
				foreach ( $message as $type => $plugin_groups ) {
					$linked_plugin_groups = array();

					// Count number of plugins in each message group to calculate singular/plural message.
					$count = count( $plugin_groups );

					// Loop through the plugin names to make the ones pulled from the .org repo linked.
					foreach ( $plugin_groups as $plugin_group_single_name ) {
						$external_url = $this->_get_plugin_data_from_name( $plugin_group_single_name, 'external_url' );
						$source       = $this->_get_plugin_data_from_name( $plugin_group_single_name, 'source' );

						if ( $external_url && preg_match( '|^http(s)?://|', $external_url ) ) {
							$linked_plugin_groups[] = '<a href="' . esc_url( $external_url ) . '" target="_blank">' . esc_html( $plugin_group_single_name ) . '</a>';
						}
						elseif ( ! $source || preg_match( '|^http://wordpress.org/extend/plugins/|', $source ) ) {
							$url = add_query_arg(
								array(
									'tab'       => 'plugin-information',
									'plugin'    => urlencode( $this->_get_plugin_data_from_name( $plugin_group_single_name ) ),
									'TB_iframe' => 'true',
									'width'     => '640',
									'height'    => '500',
								),
								self_admin_url( 'plugin-install.php' )
							);

							$linked_plugin_groups[] = '<a href="' . esc_url( $url ) . '" class="thickbox">' . esc_html( $plugin_group_single_name ) . '</a>';
						}
						else {
							$linked_plugin_groups[] = $plugin_group_single_name; // No hyperlink.
						}

						if ( isset( $linked_plugin_groups ) && (array) $linked_plugin_groups ) {
							$plugin_groups = $linked_plugin_groups;
						}
					}

					$last_plugin = array_pop( $plugin_groups ); // Pop off last name to prep for readability.
					$imploded    = empty( $plugin_groups ) ? '<em>' . $last_plugin . '</em>' : '<em>' . ( implode( ', ', $plugin_groups ) . '</em> and <em>' . $last_plugin . '</em>' );

					$rendered .= '<p>' . sprintf( translate_nooped_plural( $this->strings[ $type ], $count, 'tgmpa' ), $imploded, $count ) . '</p>';
				}

				// Setup variables to determine if action links are needed.
				$show_install_link  = $install_link ? '<a href="' . esc_url( add_query_arg( 'page', urlencode( $this->menu ), self_admin_url( $this->parent_slug ) ) ) . '">' . translate_nooped_plural( $this->strings['install_link'], $install_link_count, 'tgmpa' ) . '</a>' : '';
				$show_activate_link = $activate_link ? '<a href="' . esc_url( add_query_arg( 'page', urlencode( $this->menu ), self_admin_url( $this->parent_slug ) ) ) . '">' . translate_nooped_plural( $this->strings['activate_link'], $activate_link_count, 'tgmpa' ) . '</a>'  : '';

				// Define all of the action links.
				$action_links = apply_filters(
					'tgmpa_notice_action_links',
					array(
						'install'  => ( current_user_can( 'install_plugins' ) )  ? $show_install_link  : '',
						'activate' => ( current_user_can( 'activate_plugins' ) ) ? $show_activate_link : '',
						'dismiss'  => $this->dismissable ? '<a href="' . esc_url( add_query_arg( 'tgmpa-dismiss', 'dismiss_admin_notices' ) ) . '" class="dismiss-notice" target="_parent">' . esc_html( $this->strings['dismiss'] ) . '</a>' : '',
					)
				);

				$action_links = array_filter( (array) $action_links ); // Remove any empty array items.
				if ( ! empty( $action_links ) ) {
					$rendered .= apply_filters( 'tgmpa_notice_rendered_action_links', '<p>' . implode( ' | ', $action_links ) . '</p>' );
				}

				// Register the nag messages and prepare them to be processed.
				$nag_class = version_compare( $this->wp_version, '3.8', '<' ) ? 'updated' : 'update-nag';
				if ( ! empty( $this->strings['nag_type'] ) ) {
					add_settings_error( 'tgmpa', 'tgmpa', $rendered, sanitize_html_class( strtolower( $this->strings['nag_type'] ) ) );
				}
				else {
					add_settings_error( 'tgmpa', 'tgmpa', $rendered, $nag_class );
				}
			}

			// Admin options pages already output settings_errors, so this is to avoid duplication.
			if ( 'options-general' !== $GLOBALS['current_screen']->parent_base ) {
				$this->display_settings_errors();
			}

		}

		/**
		 * Display settings errors and remove those which have been displayed to avoid duplicate messages showing
		 *
		 * @since 2.5.0
		 */
		protected function display_settings_errors() {
			global $wp_settings_errors;

			settings_errors( 'tgmpa' );

			foreach ( (array) $wp_settings_errors as $key => $details ) {
				if ( 'tgmpa' === $details['setting'] ) {
					unset( $wp_settings_errors[ $key ] );
					break;
				}
			}
		}

		/**
		 * Add dismissable admin notices.
		 *
		 * Appends a link to the admin nag messages. If clicked, the admin notice disappears and no longer is visible to users.
		 *
		 * @since 2.1.0
		 */
		public function dismiss() {

			if ( isset( $_GET['tgmpa-dismiss'] ) ) {
				update_user_meta( get_current_user_id(), 'tgmpa_dismissed_notice_' . $this->id, 1 );
			}

		}

		/**
		 * Add individual plugin to our collection of plugins.
		 *
		 * If the required keys are not set or the plugin has already
		 * been registered, the plugin is not added.
		 *
		 * @since 2.0.0
		 *
		 * @param array $plugin Array of plugin arguments.
		 */
		public function register( $plugin ) {
			if ( ! isset( $plugin['slug'] ) || ! isset( $plugin['name'] ) ) {
				return;
			}

			foreach ( $this->plugins as $registered_plugin ) {
				if ( $plugin['slug'] === $registered_plugin['slug'] ) {
					return;
				}
			}

			$this->plugins[] = $plugin;

		}

		/**
		 * Amend default configuration settings.
		 *
		 * @since 2.0.0
		 *
		 * @param array $config Array of config options to pass as class properties.
		 */
		public function config( $config ) {

			$keys = array(
				'id',
				'default_path',
				'has_notices',
				'dismissable',
				'dismiss_msg',
				'menu',
				'parent_slug',
				'capability',
				'is_automatic',
				'message',
				'strings',
			);

			foreach ( $keys as $key ) {
				if ( isset( $config[ $key ] ) ) {
					if ( is_array( $config[ $key ] ) ) {
						$this->$key = array_merge( $this->$key, $config[ $key ] );
					}
					else {
						$this->$key = $config[ $key ];
					}
				}
			}

		}

		/**
		 * Amend action link after plugin installation.
		 *
		 * @since 2.0.0
		 *
		 * @param array $install_actions Existing array of actions.
		 * @return array                 Amended array of actions.
		 */
		public function actions( $install_actions ) {

			// Remove action links on the TGMPA install page.
			if ( $this->is_tgmpa_page() ) {
				return false;
			}

			return $install_actions;

		}

		/**
		 * Flushes the plugins cache on theme switch to prevent stale entries
		 * from remaining in the plugin table.
		 *
		 * @since 2.4.0
		 */
		public function flush_plugins_cache() {

			wp_clean_plugins_cache();

		}

		/**
		 * Set file_path key for each installed plugin.
		 *
		 * @since 2.1.0
		 */
		public function populate_file_path() {

			// Add file_path key for all plugins.
			foreach ( $this->plugins as $plugin => $values ) {
				$this->plugins[ $plugin ]['file_path'] = $this->_get_plugin_basename_from_slug( $values['slug'] );
			}

		}

		/**
		 * Helper function to extract the file path of the plugin file from the
		 * plugin slug, if the plugin is installed.
		 *
		 * @since 2.0.0
		 *
		 * @param string $slug Plugin slug (typically folder name) as provided by the developer.
		 * @return string      Either file path for plugin if installed, or just the plugin slug.
		 */
		protected function _get_plugin_basename_from_slug( $slug ) {

			$keys = array_keys( get_plugins() );

			foreach ( $keys as $key ) {
				if ( preg_match( '|^' . $slug . '/|', $key ) ) {
					return $key;
				}
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
		 * @param string $name    Name of the plugin, as it was registered.
		 * @param string $data    Optional. Array key of plugin data to return. Default is slug.
		 * @return string|boolean Plugin slug if found, false otherwise.
		 */
		public function _get_plugin_data_from_name( $name, $data = 'slug' ) {

			foreach ( $this->plugins as $values ) {
				if ( $name === $values['name'] && isset( $values[ $data ] ) ) {
					return $values[ $data ];
				}
			}

			return false;

		}

		/**
		 * Determine if we're on the TGMPA Install page.
		 *
		 * @since 2.1.0
		 *
		 * @return boolean True when on the TGMPA page, false otherwise.
		 */
		protected function is_tgmpa_page() {

			return isset( $_GET['page'] ) && $this->menu === $_GET['page'];

		}

		/**
		 * Delete dismissable nag option when theme is switched.
		 *
		 * This ensures that the user(s) is/are again reminded via nag of required
		 * and/or recommended plugins if they re-activate the theme.
		 *
		 * @since 2.1.1
		 */
		public function update_dismiss() {

			delete_metadata( 'user', null, 'tgmpa_dismissed_notice_' . $this->id, null, true );

		}

		/**
		 * Forces plugin activation if the parameter 'force_activation' is
		 * set to true.
		 *
		 * This allows theme authors to specify certain plugins that must be
		 * active at all times while using the current theme.
		 *
		 * Please take special care when using this parameter as it has the
		 * potential to be harmful if not used correctly. Setting this parameter
		 * to true will not allow the specified plugin to be deactivated unless
		 * the user switches themes.
		 *
		 * @since 2.2.0
		 */
		public function force_activation() {

			// Set file_path parameter for any installed plugins.
			$this->populate_file_path();

			$installed_plugins = get_plugins();

			foreach ( $this->plugins as $plugin ) {
				// Oops, plugin isn't there so iterate to next condition.
				if ( isset( $plugin['force_activation'] ) && $plugin['force_activation'] && ! isset( $installed_plugins[ $plugin['file_path'] ] ) ) {
					continue;
				}
				// There we go, activate the plugin.
				elseif ( isset( $plugin['force_activation'] ) && $plugin['force_activation'] && is_plugin_inactive( $plugin['file_path'] ) ) {
					activate_plugin( $plugin['file_path'] );
				}
			}

		}

		/**
		 * Forces plugin deactivation if the parameter 'force_deactivation'
		 * is set to true.
		 *
		 * This allows theme authors to specify certain plugins that must be
		 * deactivated upon switching from the current theme to another.
		 *
		 * Please take special care when using this parameter as it has the
		 * potential to be harmful if not used correctly.
		 *
		 * @since 2.2.0
		 */
		public function force_deactivation() {

			// Set file_path parameter for any installed plugins.
			$this->populate_file_path();

			foreach ( $this->plugins as $plugin ) {
				// Only proceed forward if the parameter is set to true and plugin is active.
				if ( isset( $plugin['force_deactivation'] ) && $plugin['force_deactivation'] && is_plugin_active( $plugin['file_path'] ) ) {
					deactivate_plugins( $plugin['file_path'] );
				}
			}

		}

		/**
		 * Returns the singleton instance of the class.
		 *
		 * @since 2.4.0
		 *
		 * @return object The TGM_Plugin_Activation object.
		 */
		public static function get_instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
				self::$instance = new self();
			}

			return self::$instance;

		}

	}

	if ( ! function_exists( 'load_tgm_plugin_activation' ) ) {
		/**
		 * Ensure only one instance of the class is ever invoked.
		 */
		function load_tgm_plugin_activation() {
			$GLOBALS['tgmpa'] = TGM_Plugin_Activation::get_instance();
		}
	}

	if ( did_action( 'plugins_loaded' ) ) {
		load_tgm_plugin_activation();
	}
	else {
		add_action( 'plugins_loaded', 'load_tgm_plugin_activation' );
	}
}

if ( ! function_exists( 'tgmpa' ) ) {
	/**
	 * Helper function to register a collection of required plugins.
	 *
	 * @since 2.0.0
	 * @api
	 *
	 * @param array $plugins An array of plugin arrays.
	 * @param array $config  Optional. An array of configuration values.
	 */
	function tgmpa( $plugins, $config = array() ) {
		$instance = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );

		foreach ( $plugins as $plugin ) {
			call_user_func( array( $instance, 'register' ), $plugin );
		}

		if ( ! empty( $config ) && is_array( $config ) ) {
			call_user_func( array( $instance, 'config' ), $config );
		}
	}
}

/**
 * WP_List_Table isn't always available. If it isn't available,
 * we load it here.
 *
 * @since 2.2.0
 */
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'TGMPA_List_Table' ) ) {
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
	 * @author  Thomas Griffin
	 * @author  Gary Jones
	 */
	class TGMPA_List_Table extends WP_List_Table {

		/**
		 * TGMPA instance
		 *
		 * @since 2.5.0
		 *
		 * @var object
		 */
		protected $tgmpa;

		/**
		 * References parent constructor and sets defaults for class.
		 *
		 * @since 2.2.0
		 */
		public function __construct() {
			$this->tgmpa = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );

			add_filter( 'tgmpa_plugin_table_items', array( $this, 'sort_table_items' ) );

			parent::__construct(
				array(
					'singular' => 'plugin',
					'ajax'     => false,
				)
			);
		}

		/**
		 * Gathers and renames all of our plugin information to be used by
		 * WP_List_Table to create our table.
		 *
		 * @since 2.2.0
		 *
		 * @return array $table_data Information for use in table.
		 */
		protected function _gather_plugin_data() {

			// Load thickbox for plugin links.
			$this->tgmpa->admin_init();
			$this->tgmpa->thickbox();

			// Prep variables for use and grab list of all installed plugins.
			$table_data        = array();
			$i                 = 0;
			$installed_plugins = get_plugins();

			foreach ( $this->tgmpa->plugins as $plugin ) {
				if ( is_plugin_active( $plugin['file_path'] ) || ( isset( $plugin['is_callable'] ) && is_callable( $plugin['is_callable'] ) ) ) {
					continue; // No need to display plugins if they are installed and activated.
				}

				$table_data[ $i ]['sanitized_plugin'] = $plugin['name'];
				$table_data[ $i ]['slug']             = $this->_get_plugin_data_from_name( $plugin['name'] );

				$external_url = $this->_get_plugin_data_from_name( $plugin['name'], 'external_url' );
				$source       = $this->_get_plugin_data_from_name( $plugin['name'], 'source' );

				if ( $external_url && preg_match( '|^http(s)?://|', $external_url ) ) {
					$table_data[ $i ]['plugin'] = '<strong><a href="' . esc_url( $external_url ) . '" target="_blank">' . esc_html( $plugin['name'] ) . '</a></strong>';
				}
				elseif ( ! $source || preg_match( '|^http://wordpress.org/extend/plugins/|', $source ) ) {
					$url = add_query_arg(
						array(
							'tab'       => 'plugin-information',
							'plugin'    => urlencode( $this->_get_plugin_data_from_name( $plugin['name'] ) ),
							'TB_iframe' => 'true',
							'width'     => '640',
							'height'    => '500',
						),
						self_admin_url( 'plugin-install.php' )
					);

					$table_data[ $i ]['plugin'] = '<strong><a href="' . esc_url( $url ) . '" class="thickbox">' . esc_html( $plugin['name'] ) . '</a></strong>';
				}
				else {
					$table_data[ $i ]['plugin'] = '<strong>' . $plugin['name'] . '</strong>'; // No hyperlink.
				}

				if ( isset( $table_data[ $i ]['plugin'] ) && (array) $table_data[ $i ]['plugin'] ) {
					$plugin['name'] = $table_data[ $i ]['plugin'];
				}

				if ( ! empty( $plugin['source'] ) ) {
					if ( preg_match( '|^http(s)?://|', $plugin['source'] ) ) {
						// The plugin must be from a private repository.
						$table_data[ $i ]['source'] = __( 'Private Repository', 'tgmpa' );
					}
					else {
						// The plugin is pre-packaged with the theme.
						$table_data[ $i ]['source'] = __( 'Pre-Packaged', 'tgmpa' );
					}
				}
				// The plugin is from the WordPress repository.
				else {
					$table_data[ $i ]['source'] = __( 'WordPress Repository', 'tgmpa' );
				}

				$table_data[ $i ]['type'] = isset( $plugin['required'] ) && $plugin['required'] ? __( 'Required', 'tgmpa' ) : __( 'Recommended', 'tgmpa' );

				if ( ! isset( $installed_plugins[ $plugin['file_path'] ] ) ) {
					$table_data[ $i ]['status'] = sprintf( '%1$s', __( 'Not Installed', 'tgmpa' ) );
				}
				elseif ( is_plugin_inactive( $plugin['file_path'] ) ) {
					$table_data[ $i ]['status'] = sprintf( '%1$s', __( 'Installed But Not Activated', 'tgmpa' ) );
				}

				$table_data[ $i ]['file_path'] = $plugin['file_path'];
				$table_data[ $i ]['url']       = isset( $plugin['source'] ) ? $plugin['source'] : 'repo';

				$table_data[ $i ] = apply_filters( 'tgmpa_table_data_item', $table_data[ $i ], $plugin );

				$i++;
			}

			return $table_data;

		}

		/**
		 * Sort plugins by Required/Recommended type and by alphabetical plugin name within each type.
		 *
		 * @since 2.5.0
		 *
		 * @param array $items
		 *
		 * @return array
		 */
		public function sort_table_items( $items ) {

			$type = array();
			$name = array();

			foreach ( $items as $i => $plugin ) {
				$type[ $i ] = $plugin['type'];
				$name[ $i ] = $plugin['sanitized_plugin'];
			}

			array_multisort( $type, SORT_DESC, $name, SORT_ASC, $items );

			return $items;

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
		 * @param string $name Name of the plugin, as it was registered.
		 * @param string $data Optional. Array key of plugin data to return. Default is slug.
		 * @return string|boolean Plugin slug if found, false otherwise.
		 */
		protected function _get_plugin_data_from_name( $name, $data = 'slug' ) {

			return $this->tgmpa->_get_plugin_data_from_name( $name, $data );
		}

		/**
		 * Create default columns to display important plugin information
		 * like type, action and status.
		 *
		 * @since 2.2.0
		 *
		 * @param array $item         Array of item data.
		 * @param string $column_name The name of the column.
		 *
		 * @return string
		 */
		public function column_default( $item, $column_name ) {

			return $item[ $column_name ];

		}

		/**
		 * Create default title column along with action links of 'Install'
		 * and 'Activate'.
		 *
		 * @since 2.2.0
		 *
		 * @param array $item Array of item data.
		 * @return string     The action hover links.
		 */
		public function column_plugin( $item ) {

			$installed_plugins = get_plugins();
			$actions           = array();

			// We need to display the 'Install' hover link.
			if ( ! is_plugin_active( $item['file_path'] ) && ! isset( $installed_plugins[ $item['file_path'] ] ) ) {
				$install_nonce_url = wp_nonce_url(
					add_query_arg(
						array(
							'page'          => urlencode( $this->tgmpa->menu ),
							'plugin'        => urlencode( $item['slug'] ),
							'plugin_name'   => urlencode( $item['sanitized_plugin'] ),
							'plugin_source' => urlencode( $item['url'] ),
							'tgmpa-install' => 'install-plugin',
						),
						self_admin_url( $this->tgmpa->parent_slug )
					),
					'tgmpa-install'
				);

				$actions = array(
					'install' => sprintf(
						'<a href="%1$s">' . esc_html_x( 'Install %2$s', '%2$s = plugin name in screen reader markup', 'tgmpa' ) . '</a>',
						esc_url( $install_nonce_url ),
						'<span class="screen-reader-text">' . esc_html( $item['sanitized_plugin'] ) . '</span>'
					),
				);
			}
			// We need to display the 'Activate' hover link.
			elseif ( is_plugin_inactive( $item['file_path'] ) ) {
				$activate_url = add_query_arg(
					array(
						'page'                 => urlencode( $this->tgmpa->menu ),
						'plugin'               => urlencode( $item['slug'] ),
						'plugin_name'          => urlencode( $item['sanitized_plugin'] ),
						'plugin_source'        => urlencode( $item['url'] ),
						'tgmpa-activate'       => 'activate-plugin',
						'tgmpa-activate-nonce' => urlencode( wp_create_nonce( 'tgmpa-activate' ) ),
					),
					self_admin_url( $this->tgmpa->parent_slug )
				);

				$actions = array(
					'activate' => sprintf(
						'<a href="%1$s">' . esc_html_x( 'Activate %2$s', '%2$s = plugin name in screen reader markup', 'tgmpa' ) . '</a>',
						esc_url( $activate_url ),
						'<span class="screen-reader-text">' . esc_html( $item['sanitized_plugin'] ) . '</span>'
					),
				);
			}

			$prefix  = ( defined( 'WP_NETWORK_ADMIN' ) && WP_NETWORK_ADMIN ) ? 'network_admin_' : '';
			$actions = apply_filters( "tgmpa_{$prefix}plugin_action_links", array_filter( $actions ), $item['slug'] );

			return sprintf( '%1$s %2$s', $item['plugin'], $this->row_actions( $actions ) );

		}

		/**
		 * Required for bulk installing.
		 *
		 * Adds a checkbox for each plugin.
		 *
		 * @since 2.2.0
		 *
		 * @param array $item Array of item data.
		 * @return string     The input checkbox with all necessary info.
		 */
		public function column_cb( $item ) {

			$plugin_url = $item['url']; // 'repo' (no escaping needed), URL or file path
			if ( __( 'Private Repository', 'tgmpa' ) === $item['source'] ) {
				// Escape external URLs
				$plugin_url = esc_url( $plugin_url );
			}
			elseif ( __( 'Pre-Packaged', 'tgmpa' ) === $item['source'] ) {
				// Encode file path for use in attribute
				$plugin_url = urlencode( $this->tgmpa->default_path . $plugin_url );
			}

			$value = $item['file_path'] . ',' . $plugin_url . ',' . $item['sanitized_plugin'];
			return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" id="%3$s" />', esc_attr( $this->_args['singular'] ), esc_attr( $value ), esc_attr( $item['sanitized_plugin'] ) );

		}

		/**
		 * Sets default message within the plugins table if no plugins
		 * are left for interaction.
		 *
		 * Hides the menu item to prevent the user from clicking and
		 * getting a permissions error.
		 *
		 * @since 2.2.0
		 */
		public function no_items() {

			printf( wp_kses_post( __( 'No plugins to install or activate. <a href="%1$s">Return to the Dashboard</a>', 'tgmpa' ) ), esc_url( self_admin_url() ) );
			echo '<style type="text/css">#adminmenu .wp-submenu li.current { display: none !important; }</style>';

		}

		/**
		 * Output all the column information within the table.
		 *
		 * @since 2.2.0
		 *
		 * @return array $columns The column names.
		 */
		public function get_columns() {

			$columns = array(
				'cb'     => '<input type="checkbox" />',
				'plugin' => __( 'Plugin', 'tgmpa' ),
				'source' => __( 'Source', 'tgmpa' ),
				'type'   => __( 'Type', 'tgmpa' ),
				'status' => __( 'Status', 'tgmpa' ),
			);

			return apply_filters( 'tgmpa_table_columns', $columns );

		}

		/**
		 * Defines all types of bulk actions for handling
		 * registered plugins.
		 *
		 * @since 2.2.0
		 *
		 * @return array $actions The bulk actions for the plugin install table.
		 */
		public function get_bulk_actions() {

			$actions = array(
				'tgmpa-bulk-install'  => __( 'Install', 'tgmpa' ),
				'tgmpa-bulk-activate' => __( 'Activate', 'tgmpa' ),
			);

			return $actions;

		}

		/**
		 * Processes bulk installation and activation actions.
		 *
		 * The bulk installation process looks either for the $_POST
		 * information or for the plugin info within the $_GET variable if
		 * a user has to use WP_Filesystem to enter their credentials.
		 *
		 * @since 2.2.0
		 */
		public function process_bulk_actions() {

			// Bulk installation process.
			if ( 'tgmpa-bulk-install' === $this->current_action() ) {
				check_admin_referer( 'bulk-' . $this->_args['plural'] );

				// Prep variables to be populated.
				$plugins_to_install = array();
				$plugin_installs    = array();
				$plugin_path        = array();
				$plugin_name        = array();

				// Look first to see if information has been passed via WP_Filesystem.
				if ( isset( $_GET['plugins'] ) ) {
					$plugins = explode( ',', stripslashes( $_GET['plugins'] ) );
				}
				// Looks like the user can use the direct method, take from $_POST.
				elseif ( isset( $_POST['plugin'] ) ) {
					$plugins = (array) $_POST['plugin'];
				}
				// Nothing has been submitted.
				else {
					$plugins = array();
				}

				// Grab information from $_POST if available.
				if ( isset( $_POST['plugin'] ) ) {
					foreach ( $plugins as $plugin_data ) {
						$plugins_to_install[] = explode( ',', $plugin_data );
					}

					foreach ( $plugins_to_install as $plugin_data ) {
						$plugin_installs[] = $plugin_data[0];
						$plugin_path[]     = $plugin_data[1];
						$plugin_name[]     = $plugin_data[2];
					}
				}
				// Information has been passed via $_GET.
				else {
					foreach ( $plugins as $key => $value ) {
						// Grab plugin slug for each plugin.
						if ( 0 === ( $key % 3 ) || 0 === $key ) {
							$plugins_to_install[] = $value;
							$plugin_installs[]    = $value;
						}
					}
				}

				// Look first to see if information has been passed via WP_Filesystem.
				if ( isset( $_GET['plugin_paths'] ) ) {
					$plugin_paths = explode( ',', stripslashes( $_GET['plugin_paths'] ) );
				}
				// Looks like the user doesn't need to enter his FTP credentials.
				elseif ( isset( $_POST['plugin'] ) ) {
					$plugin_paths = (array) $plugin_path;
				}
				// Nothing has been submitted.
				else {
					$plugin_paths = array();
				}

				// Look first to see if information has been passed via WP_Filesystem.
				if ( isset( $_GET['plugin_names'] ) ) {
					$plugin_names = explode( ',', stripslashes( $_GET['plugin_names'] ) );
				}
				// Looks like the user doesn't need to enter his FTP credentials.
				elseif ( isset( $_POST['plugin'] ) ) {
					$plugin_names = (array) $plugin_name;
				}
				// Nothing has been submitted.
				else {
					$plugin_names = array();
				}

				// Loop through plugin slugs and remove already installed plugins from the list.
				$i = 0;
				foreach ( $plugin_installs as $key => $plugin ) {
					if ( preg_match( '|.php$|', $plugin ) ) {
						unset( $plugin_installs[ $key ] );

						// If the plugin path isn't in the $_GET variable, we can unset the corresponding path.
						if ( ! isset( $_GET['plugin_paths'] ) ) {
							unset( $plugin_paths[ $i ] );
						}

						// If the plugin name isn't in the $_GET variable, we can unset the corresponding name.
						if ( ! isset( $_GET['plugin_names'] ) ) {
							unset( $plugin_names[ $i ] );
						}
					}
					$i++;
				}

				// No need to proceed further if we have no plugins to install.
				if ( empty( $plugin_installs ) ) {
					echo '<div id="message" class="error"><p>', esc_html__( 'No plugins are available to be installed at this time.', 'tgmpa' ), '</p></div>';
					return false;
				}

				// Reset array indexes in case we removed already installed plugins.
				$plugin_installs = array_values( $plugin_installs );
				$plugin_paths    = array_values( $plugin_paths );
				$plugin_names    = array_values( $plugin_names );

				// If we grabbed our plugin info from $_GET, we need to decode it for use.
				$plugin_installs = array_map( 'urldecode', $plugin_installs );
				$plugin_paths    = array_map( 'urldecode', $plugin_paths );
				$plugin_names    = array_map( 'urldecode', $plugin_names );

				// Pass all necessary information via URL if WP_Filesystem is needed.
				$url = wp_nonce_url(
					add_query_arg(
						array(
							'page'          => urlencode( $this->tgmpa->menu ),
							'tgmpa-action'  => 'install-selected',
							'plugins'       => urlencode( implode( ',', $plugins ) ),
							'plugin_paths'  => urlencode( implode( ',', $plugin_paths ) ),
							'plugin_names'  => urlencode( implode( ',', $plugin_names ) ),
						),
						self_admin_url( $this->tgmpa->parent_slug )
					),
					'bulk-plugins'
				);

				$method = ''; // Leave blank so WP_Filesystem can populate it as necessary.
				$fields = array( 'action', '_wp_http_referer', '_wpnonce' ); // Extra fields to pass to WP_Filesystem.

				if ( false === ( $creds = request_filesystem_credentials( esc_url_raw( $url ), $method, false, false, $fields ) ) ) {
					return true;
				}

				if ( ! WP_Filesystem( $creds ) ) {
					request_filesystem_credentials( esc_url_raw( $url ), $method, true, false, $fields ); // Setup WP_Filesystem.
					return true;
				}

				require_once ABSPATH . 'wp-admin/includes/plugin-install.php'; // Need for plugins_api
				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php'; // Need for upgrade classes

				// Store all information in arrays since we are processing a bulk installation.
				$api     = array();
				$sources = array();

				// Loop through each plugin to install and try to grab information from WordPress API, if not create 'tgmpa-empty' scalar.
				$i = 0;
				foreach ( $plugin_installs as $plugin ) {
					$temp = plugins_api( 'plugin_information', array( 'slug' => $plugin, 'fields' => array( 'sections' => false ) ) );

					$api[ $i ] = (object) 'tgmpa-empty';
					if ( ! is_wp_error( $temp ) ) {
						$api[ $i ] = $temp;
					}
					$i++;
				}
				unset( $plugin, $temp );

				if ( is_wp_error( $api ) ) {
					if ( true === WP_DEBUG ) {
						wp_die( esc_html( $this->tgmpa->strings['oops'] ) . var_dump( $api ) ); // wpcs: xss ok
					}
					else {
						wp_die( esc_html( $this->tgmpa->strings['oops'] ) );
					}
				}

				// Capture download links from $api or set install link to pre-packaged/private repo.
				$i = 0;
				foreach ( $api as $object ) {
					$sources[ $i ] = isset( $object->download_link ) && 'repo' === $plugin_paths[ $i ] ? $object->download_link : $plugin_paths[ $i ];
					$i++;
				}

				// Finally, all the data is prepared to be sent to the installer.
				$url   = esc_url_raw( add_query_arg( array( 'page' => urlencode( $this->tgmpa->menu ) ), self_admin_url( $this->tgmpa->parent_slug ) ) );
				$nonce = 'bulk-plugins';
				$names = $plugin_names;

				// Create a new instance of TGM_Bulk_Installer.
				$installer = new TGM_Bulk_Installer( new TGM_Bulk_Installer_Skin( compact( 'url', 'nonce', 'names' ) ) );

				// Wrap the install process with the appropriate HTML.
				echo '<div class="tgmpa wrap">',
					'<h2>', esc_html( get_admin_page_title() ), '</h2>';

				// Process the bulk installation submissions.
				// Perform the action and install the plugin from the $source urldecode().
				add_filter( 'upgrader_source_selection', array( $this->tgmpa, 'maybe_adjust_source_dir' ), 1, 3 );
				$installer->bulk_install( $sources );
				remove_filter( 'upgrader_source_selection', array( $this->tgmpa, 'maybe_adjust_source_dir' ), 1, 3 );

				echo '</div>';

				return true;
			}

			// Bulk activation process.
			if ( 'tgmpa-bulk-activate' === $this->current_action() ) {
				check_admin_referer( 'bulk-' . $this->_args['plural'] );

				// Grab plugin data from $_POST.
				$plugins             = isset( $_POST['plugin'] ) ? (array) $_POST['plugin'] : array();
				$plugins_to_activate = array();

				// Split plugin value into array with plugin file path, plugin source and plugin name.
				foreach ( $plugins as $plugin ) {
					$plugins_to_activate[] = explode( ',', $plugin );
				}

				foreach ( $plugins_to_activate as $i => $array ) {
					if ( ! preg_match( '|.php$|', $array[0] ) ) {
						unset( $plugins_to_activate[ $i ] );
					}
				}

				// Return early if there are no plugins to activate.
				if ( empty( $plugins_to_activate ) ) {
					echo '<div id="message" class="error"><p>', esc_html__( 'No plugins are available to be activated at this time.', 'tgmpa' ), '</p></div>';
					return false;
				}

				$plugins      = array();
				$plugin_names = array();

				foreach ( $plugins_to_activate as $plugin_string ) {
					$plugins[]      = $plugin_string[0];
					$plugin_names[] = $plugin_string[2];
				}

				$count       = count( $plugin_names ); // Count so we can use _n function.
				$last_plugin = array_pop( $plugin_names ); // Pop off last name to prep for readability.
				$imploded    = empty( $plugin_names ) ? '<strong>' . $last_plugin . '</strong>' : '<strong>' . ( implode( ', ', $plugin_names ) . '</strong> and <strong>' . $last_plugin . '</strong>.' );

				// Now we are good to go - let's start activating plugins.
				$activate = activate_plugins( $plugins );

				if ( is_wp_error( $activate ) ) {
					echo '<div id="message" class="error"><p>', wp_kses_post( $activate->get_error_message() ), '</p></div>';
				}
				else {
					printf( '<div id="message" class="updated"><p>%1$s %2$s.</p></div>', esc_html( _n( 'The following plugin was activated successfully:', 'The following plugins were activated successfully:', $count, 'tgmpa' ) ), wp_kses_post( $imploded ) );
				}

				// Update recently activated plugins option.
				$recent = (array) get_option( 'recently_activated' );

				foreach ( $plugins as $plugin => $time ) {
					if ( isset( $recent[ $plugin ] ) ) {
						unset( $recent[ $plugin ] );
					}
				}

				update_option( 'recently_activated', $recent );

				unset( $_POST ); // Reset the $_POST variable in case user wants to perform one action after another.

				return true;
			}

			return false;
		}

		/**
		 * Prepares all of our information to be outputted into a usable table.
		 *
		 * @since 2.2.0
		 */
		public function prepare_items() {

			$columns               = $this->get_columns(); // Get all necessary column information.
			$hidden                = array(); // No columns to hide, but we must set as an array.
			$sortable              = array(); // No reason to make sortable columns.
			$this->_column_headers = array( $columns, $hidden, $sortable ); // Get all necessary column headers.

			// Process our bulk actions here.
			$this->process_bulk_actions();

			// Store all of our plugin data into $items array so WP_List_Table can use it.
			$this->items = apply_filters( 'tgmpa_plugin_table_items', $this->_gather_plugin_data() );

		}

	}
}

/**
 * The WP_Upgrader file isn't always available. If it isn't available,
 * we load it here.
 *
 * We check to make sure no action or activation keys are set so that WordPress
 * does not try to re-include the class when processing upgrades or installs outside
 * of the class.
 *
 * @since 2.2.0
 */
add_action( 'admin_init', 'tgmpa_load_bulk_installer' );
if ( ! function_exists( 'tgmpa_load_bulk_installer' ) ) {
	/**
	 * Load bulk installer
	 */
	function tgmpa_load_bulk_installer() {
		// Get TGMPA class instance
		$tgmpa_instance = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );

		if ( ! class_exists( 'WP_Upgrader' ) && ( isset( $_GET['page'] ) && $tgmpa_instance->menu === $_GET['page'] ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

			if ( ! class_exists( 'TGM_Bulk_Installer' ) ) {
				/**
				 * Installer class to handle bulk plugin installations.
				 *
				 * Extends WP_Upgrader and customizes to suit the installation of multiple
				 * plugins.
				 *
				 * @since 2.2.0
				 *
				 * @package TGM-Plugin-Activation
				 * @author  Thomas Griffin
				 * @author  Gary Jones
				 */
				class TGM_Bulk_Installer extends WP_Upgrader {

					/**
					 * Holds result of bulk plugin installation.
					 *
					 * @since 2.2.0
					 *
					 * @var string
					 */
					public $result;

					/**
					 * Flag to check if bulk installation is occurring or not.
					 *
					 * @since 2.2.0
					 *
					 * @var boolean
					 */
					public $bulk = false;

					/**
					 * TGMPA instance
					 *
					 * @since 2.5.0
					 *
					 * @var object
					 */
					protected $tgmpa;

					/**
					 * References parent constructor and sets defaults for class.
					 *
					 * @since 2.2.0
					 */
					public function __construct( $skin = null ) {
						// Get TGMPA class instance
						$this->tgmpa = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );

						parent::__construct( $skin );
					}

					/**
					 * Processes the bulk installation of plugins.
					 *
					 * @since 2.2.0
					 *
					 * @param array $packages The plugin sources needed for installation.
					 * @return string|boolean Install confirmation messages on success, false on failure.
					 */
					public function bulk_install( $packages ) {

						// Pass installer skin object and set bulk property to true.
						$this->init();
						$this->bulk = true;

						// Set install strings and automatic activation strings (if config option is set to true).
						$this->install_strings();
						if ( $this->tgmpa->is_automatic ) {
							$this->activate_strings();
						}

						// Run the header string to notify user that the process has begun.
						$this->skin->header();

						// Connect to the Filesystem.
						$res = $this->fs_connect( array( WP_CONTENT_DIR, WP_PLUGIN_DIR ) );
						if ( ! $res ) {
							$this->skin->footer();
							return false;
						}

						// Set the bulk header and prepare results array.
						$this->skin->bulk_header();
						$results = array();

						// Get the total number of packages being processed and iterate as each package is successfully installed.
						$this->update_count   = count( $packages );
						$this->update_current = 0;

						// Loop through each plugin and process the installation.
						foreach ( $packages as $plugin ) {
							$this->update_current++; // Increment counter.

							// Do the plugin install.
							$result = $this->run(
								array(
									'package'           => $plugin, // The plugin source.
									'destination'       => WP_PLUGIN_DIR, // The destination dir.
									'clear_destination' => false, // Do we want to clear the destination or not?
									'clear_working'     => true, // Remove original install file.
									'is_multi'          => true, // Are we processing multiple installs?
									'hook_extra'        => array( 'plugin' => $plugin ), // Pass plugin source as extra data.
								)
							);

							// Store installation results in result property.
							$results[ $plugin ] = $this->result;

							// Prevent credentials auth screen from displaying multiple times.
							if ( false === $result ) {
								break;
							}
						}

						// Pass footer skin strings.
						$this->skin->bulk_footer();
						$this->skin->footer();

						// Return our results.
						return $results;

					}

					/**
					 * Performs the actual installation of each plugin.
					 *
					 * This method also activates the plugin in the automatic flag has been
					 * set to true for the TGMPA class.
					 *
					 * @since 2.2.0
					 *
					 * @param array $options The installation config options
					 *
					 * @return null|array Return early if error, array of installation data on success
					 */
					public function run( $options ) {

						// Default config options.
						$defaults = array(
							'package'           => '',
							'destination'       => '',
							'clear_destination' => false,
							'clear_working'     => true,
							'is_multi'          => false,
							'hook_extra'        => array(),
						);

						// Parse default options with config options from $this->bulk_upgrade.
						$options = wp_parse_args( $options, $defaults );

						// Connect to the Filesystem.
						$res = $this->fs_connect( array( WP_CONTENT_DIR, $options['destination'] ) );
						if ( ! $res ) {
							return false;
						}

						// Return early if there is an error connecting to the Filesystem.
						if ( is_wp_error( $res ) ) {
							$this->skin->error( $res );
							return $res;
						}

						// Call $this->header separately if running multiple times.
						if ( ! $options['is_multi'] ) {
							$this->skin->header();
						}

						// Set strings before the package is installed.
						$this->skin->before();

						// Download the package (this just returns the filename of the file if the package is a local file).
						$download = $this->download_package( $options['package'] );
						if ( is_wp_error( $download ) ) {
							$this->skin->error( $download );
							$this->skin->after();
							return $download;
						}

						// Don't accidentally delete a local file.
						$delete_package = ( $download !== $options['package'] );

						// Unzip file into a temporary working directory.
						$working_dir = $this->unpack_package( $download, $delete_package );
						if ( is_wp_error( $working_dir ) ) {
							$this->skin->error( $working_dir );
							$this->skin->after();
							return $working_dir;
						}

						// Install the package into the working directory with all passed config options.
						$result = $this->install_package(
							array(
								'source'            => $working_dir,
								'destination'       => $options['destination'],
								'clear_destination' => $options['clear_destination'],
								'clear_working'     => $options['clear_working'],
								'hook_extra'        => $options['hook_extra'],
							)
						);

						// Pass the result of the installation.
						$this->skin->set_result( $result );

						// Set correct strings based on results.
						if ( is_wp_error( $result ) ) {
							$this->skin->error( $result );
							$this->skin->feedback( 'process_failed' );
						}
						// The plugin install is successful.
						else {
							$this->skin->feedback( 'process_success' );
						}

						// Only process the activation of installed plugins if the automatic flag is set to true.
						if ( $this->tgmpa->is_automatic ) {
							// Flush plugins cache so we can make sure that the installed plugins list is always up to date.
							wp_clean_plugins_cache();

							// Get the installed plugin file and activate it.
							$plugin_info = $this->plugin_info();
							$activate    = activate_plugin( $plugin_info );

							// Re-populate the file path now that the plugin has been installed and activated.
							$this->tgmpa->populate_file_path();

							// Set correct strings based on results.
							if ( is_wp_error( $activate ) ) {
								$this->skin->error( $activate );
								$this->skin->feedback( 'activation_failed' );
							}
							// The plugin activation is successful.
							else {
								$this->skin->feedback( 'activation_success' );
							}
						}

						// Flush plugins cache so we can make sure that the installed plugins list is always up to date.
						wp_clean_plugins_cache();

						// Set install footer strings.
						$this->skin->after();
						if ( ! $options['is_multi'] ) {
							$this->skin->footer();
						}

						return $result;

					}

					/**
					 * Sets the correct install strings for the installer skin to use.
					 *
					 * @since 2.2.0
					 */
					public function install_strings() {

						$this->strings['no_package']          = __( 'Install package not available.', 'tgmpa' );
						$this->strings['downloading_package'] = __( 'Downloading install package from <span class="code">%s</span>&#8230;', 'tgmpa' );
						$this->strings['unpack_package']      = __( 'Unpacking the package&#8230;', 'tgmpa' );
						$this->strings['installing_package']  = __( 'Installing the plugin&#8230;', 'tgmpa' );
						$this->strings['process_failed']      = __( 'Plugin install failed.', 'tgmpa' );
						$this->strings['process_success']     = __( 'Plugin installed successfully.', 'tgmpa' );

					}

					/**
					 * Sets the correct activation strings for the installer skin to use.
					 *
					 * @since 2.2.0
					 */
					public function activate_strings() {

						$this->strings['activation_failed']  = __( 'Plugin activation failed.', 'tgmpa' );
						$this->strings['activation_success'] = __( 'Plugin activated successfully.', 'tgmpa' );

					}

					/**
					 * Grabs the plugin file from an installed plugin.
					 *
					 * @since 2.2.0
					 *
					 * @return string|boolean Return plugin file on success, false on failure
					 */
					public function plugin_info() {

						// Return false if installation result isn't an array or the destination name isn't set.
						if ( ! is_array( $this->result ) ) {
							return false;
						}

						if ( empty( $this->result['destination_name'] ) ) {
							return false;
						}

						/// Get the installed plugin file or return false if it isn't set.
						$plugin = get_plugins( '/' . $this->result['destination_name'] );
						if ( empty( $plugin ) ) {
							return false;
						}

						// Assume the requested plugin is the first in the list.
						$plugin_files = array_keys( $plugin );

						return $this->result['destination_name'] . '/' . $plugin_files[0];

					}

				}
			}

			if ( ! class_exists( 'TGM_Bulk_Installer_Skin' ) ) {
				/**
				 * Installer skin to set strings for the bulk plugin installations..
				 *
				 * Extends Bulk_Upgrader_Skin and customizes to suit the installation of multiple
				 * plugins.
				 *
				 * @since 2.2.0
				 *
				 * @package TGM-Plugin-Activation
				 * @author  Thomas Griffin
				 * @author  Gary Jones
				 */
				class TGM_Bulk_Installer_Skin extends Bulk_Upgrader_Skin {

					/**
					 * Holds plugin info for each individual plugin installation.
					 *
					 * @since 2.2.0
					 *
					 * @var array
					 */
					public $plugin_info = array();

					/**
					 * Holds names of plugins that are undergoing bulk installations.
					 *
					 * @since 2.2.0
					 *
					 * @var array
					 */
					public $plugin_names = array();

					/**
					 * Integer to use for iteration through each plugin installation.
					 *
					 * @since 2.2.0
					 *
					 * @var integer
					 */
					public $i = 0;

					/**
					 * TGMPA instance
					 *
					 * @since 2.5.0
					 *
					 * @var object
					 */
					protected $tgmpa;

					/**
					 * Constructor. Parses default args with new ones and extracts them for use.
					 *
					 * @since 2.2.0
					 *
					 * @param array $args Arguments to pass for use within the class.
					 */
					public function __construct( $args = array() ) {
						// Get TGMPA class instance
						$this->tgmpa = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );

						// Parse default and new args.
						$defaults = array( 'url' => '', 'nonce' => '', 'names' => array() );
						$args     = wp_parse_args( $args, $defaults );

						// Set plugin names to $this->plugin_names property.
						$this->plugin_names = $args['names'];

						// Extract the new args.
						parent::__construct( $args );

					}

					/**
					 * Sets install skin strings for each individual plugin.
					 *
					 * Checks to see if the automatic activation flag is set and uses the
					 * the proper strings accordingly.
					 *
					 * @since 2.2.0
					 */
					public function add_strings() {

						$this->upgrader->strings['skin_update_failed_error'] = __( 'An error occurred while installing %1$s: <strong>%2$s</strong>.', 'tgmpa' );
						$this->upgrader->strings['skin_update_failed']       = __( 'The installation of %1$s failed.', 'tgmpa' );

						// Automatic activation strings.
						if ( $this->tgmpa->is_automatic ) {
							$this->upgrader->strings['skin_upgrade_start']        = __( 'The installation and activation process is starting. This process may take a while on some hosts, so please be patient.', 'tgmpa' );
							$this->upgrader->strings['skin_update_successful']    = __( '%1$s installed and activated successfully.', 'tgmpa' ) . ' <a href="#" class="hide-if-no-js" onclick="%2$s"><span>' . esc_html__( 'Show Details', 'tgmpa' ) . '</span><span class="hidden">' . esc_html__( 'Hide Details', 'tgmpa' ) . '</span>.</a>';
							$this->upgrader->strings['skin_upgrade_end']          = __( 'All installations and activations have been completed.', 'tgmpa' );
							$this->upgrader->strings['skin_before_update_header'] = __( 'Installing and Activating Plugin %1$s (%2$d/%3$d)', 'tgmpa' );
						}
						// Default installation strings.
						else {
							$this->upgrader->strings['skin_upgrade_start']        = __( 'The installation process is starting. This process may take a while on some hosts, so please be patient.', 'tgmpa' );
							$this->upgrader->strings['skin_update_successful']    = esc_html__( '%1$s installed successfully.', 'tgmpa' ) . ' <a href="#" class="hide-if-no-js" onclick="%2$s"><span>' . esc_html__( 'Show Details', 'tgmpa' ) . '</span><span class="hidden">' . esc_html__( 'Hide Details', 'tgmpa' ) . '</span>.</a>';
							$this->upgrader->strings['skin_upgrade_end']          = __( 'All installations have been completed.', 'tgmpa' );
							$this->upgrader->strings['skin_before_update_header'] = __( 'Installing Plugin %1$s (%2$d/%3$d)', 'tgmpa' );
						}

					}

					/**
					 * Outputs the header strings and necessary JS before each plugin installation.
					 *
					 * @since 2.2.0
					 */
					public function before( $title = '' ) {

						// We are currently in the plugin installation loop, so set to true.
						$this->in_loop = true;

						printf( '<h4>' . wp_kses_post( $this->upgrader->strings['skin_before_update_header'] ). ' <img alt="" src="' . esc_url( admin_url( 'images/wpspin_light.gif' ) ) . '" class="hidden waiting-' . esc_attr( $this->upgrader->update_current ) . '" style="vertical-align:middle;" /></h4>', esc_html( $this->plugin_names[ $this->i ] ), absint( $this->upgrader->update_current ), absint( $this->upgrader->update_count ) );
						echo '<script type="text/javascript">jQuery(\'.waiting-', esc_js( $this->upgrader->update_current ), '\').show();</script>';
						echo '<div class="update-messages hide-if-js" id="progress-', esc_attr( $this->upgrader->update_current ), '"><p>';

						// Flush header output buffer.
						$this->before_flush_output();

					}

					/**
					 * Outputs the footer strings and necessary JS after each plugin installation.
					 *
					 * Checks for any errors and outputs them if they exist, else output
					 * success strings.
					 *
					 * @since 2.2.0
					 */
					public function after( $title = '' ) {

						// Close install strings.
						echo '</p></div>';

						// Output error strings if an error has occurred.
						if ( $this->error || ! $this->result ) {
							if ( $this->error ) {
								echo '<div class="error"><p>', sprintf( wp_kses_post( $this->upgrader->strings['skin_update_failed_error'] ), esc_html( $this->plugin_names[ $this->i ] ), wp_kses_post( $this->error ) ), '</p></div>';
							}
							else {
								echo '<div class="error"><p>', sprintf( wp_kses_post( $this->upgrader->strings['skin_update_failed'] ), esc_html( $this->plugin_names[ $this->i ] ) ), '</p></div>';
							}

							echo '<script type="text/javascript">jQuery(\'#progress-', esc_js( $this->upgrader->update_current ), '\').show();</script>';
						}

						// If the result is set and there are no errors, success!
						if ( ! empty( $this->result ) && ! is_wp_error( $this->result ) ) {
							echo '<div class="updated"><p>', sprintf( $this->upgrader->strings['skin_update_successful'] /* pre-escaped */, esc_html( $this->plugin_names[ $this->i ] ), 'jQuery(\'#progress-' . esc_js( $this->upgrader->update_current ) . '\').toggle();jQuery(\'span\', this).toggle(); return false;' ), '</p></div>';
							echo '<script type="text/javascript">jQuery(\'.waiting-', esc_js( $this->upgrader->update_current ), '\').hide();</script>';
						}

						// Set in_loop and error to false and flush footer output buffer.
						$this->reset();
						$this->after_flush_output();

					}

					/**
					 * Outputs links after bulk plugin installation is complete.
					 *
					 * @since 2.2.0
					 */
					public function bulk_footer() {

						// Serve up the string to say installations (and possibly activations) are complete.
						parent::bulk_footer();

						// Flush plugins cache so we can make sure that the installed plugins list is always up to date.
						wp_clean_plugins_cache();

						// Display message based on if all plugins are now active or not.
						$complete = true;
						foreach ( $this->tgmpa->plugins as $plugin ) {
							if ( ! is_plugin_active( $plugin['file_path'] ) ) {
								echo '<p><a href="', esc_url( add_query_arg( 'page', urlencode( $this->tgmpa->menu ), self_admin_url( $this->tgmpa->parent_slug ) ) ), '" target="_parent">', esc_html( $this->tgmpa->strings['return'] ), '</a></p>';
								$complete = false;
								break;
							}
						}

						// All plugins are active, so we display the complete string and hide the menu to protect users.
						if ( true === $complete ) {
							echo '<p>', sprintf( esc_html( $this->tgmpa->strings['complete'] ), '<a href="' . esc_url( self_admin_url() ) . '">' . esc_html__( 'Return to the Dashboard', 'tgmpa' ) . '</a>' ), '</p>';
							echo '<style type="text/css">#adminmenu .wp-submenu li.current { display: none !important; }</style>';
						}

					}

					/**
					 * Flush header output buffer.
					 *
					 * @since 2.2.0
					 */
					public function before_flush_output() {

						wp_ob_end_flush_all();
						flush();

					}

					/**
					 * Flush footer output buffer and iterate $this->i to make sure the
					 * installation strings reference the correct plugin.
					 *
					 * @since 2.2.0
					 */
					public function after_flush_output() {

						wp_ob_end_flush_all();
						flush();
						$this->i++;

					}

				}
			}
		}

	}
}
