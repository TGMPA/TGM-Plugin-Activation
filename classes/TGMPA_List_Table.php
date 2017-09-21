<?php
	
	namespace TGM;
	
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
		 * TGMPA instance.
		 *
		 * @since 2.5.0
		 *
		 * @var object
		 */
		protected $tgmpa;

		/**
		 * The currently chosen view.
		 *
		 * @since 2.5.0
		 *
		 * @var string One of: 'all', 'install', 'update', 'activate'
		 */
		public $view_context = 'all';

		/**
		 * The plugin counts for the various views.
		 *
		 * @since 2.5.0
		 *
		 * @var array
		 */
		protected $view_totals = array(
			'all'      => 0,
			'install'  => 0,
			'update'   => 0,
			'activate' => 0,
		);

		/**
		 * References parent constructor and sets defaults for class.
		 *
		 * @since 2.2.0
		 */
		public function __construct() {
			$this->tgmpa = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );

			parent::__construct(
				array(
					'singular' => 'plugin',
					'plural'   => 'plugins',
					'ajax'     => false,
				)
			);

			if ( isset( $_REQUEST['plugin_status'] ) && in_array( $_REQUEST['plugin_status'], array( 'install', 'update', 'activate' ), true ) ) {
				$this->view_context = sanitize_key( $_REQUEST['plugin_status'] );
			}

			add_filter( 'tgmpa_table_data_items', array( $this, 'sort_table_items' ) );
		}

		/**
		 * Get a list of CSS classes for the <table> tag.
		 *
		 * Overruled to prevent the 'plural' argument from being added.
		 *
		 * @since 2.5.0
		 *
		 * @return array CSS classnames.
		 */
		public function get_table_classes() {
			return array( 'widefat', 'fixed' );
		}

		/**
		 * Gathers and renames all of our plugin information to be used by WP_List_Table to create our table.
		 *
		 * @since 2.2.0
		 *
		 * @return array $table_data Information for use in table.
		 */
		protected function _gather_plugin_data() {
			// Load thickbox for plugin links.
			$this->tgmpa->admin_init();
			$this->tgmpa->thickbox();

			// Categorize the plugins which have open actions.
			$plugins = $this->categorize_plugins_to_views();

			// Set the counts for the view links.
			$this->set_view_totals( $plugins );

			// Prep variables for use and grab list of all installed plugins.
			$table_data = array();
			$i          = 0;

			// Redirect to the 'all' view if no plugins were found for the selected view context.
			if ( empty( $plugins[ $this->view_context ] ) ) {
				$this->view_context = 'all';
			}

			foreach ( $plugins[ $this->view_context ] as $slug => $plugin ) {
				$table_data[ $i ]['sanitized_plugin']  = $plugin['name'];
				$table_data[ $i ]['slug']              = $slug;
				$table_data[ $i ]['plugin']            = '<strong>' . $this->tgmpa->get_info_link( $slug ) . '</strong>';
				$table_data[ $i ]['source']            = $this->get_plugin_source_type_text( $plugin['source_type'] );
				$table_data[ $i ]['type']              = $this->get_plugin_advise_type_text( $plugin['required'] );
				$table_data[ $i ]['status']            = $this->get_plugin_status_text( $slug );
				$table_data[ $i ]['installed_version'] = $this->tgmpa->get_installed_version( $slug );
				$table_data[ $i ]['minimum_version']   = $plugin['version'];
				$table_data[ $i ]['available_version'] = $this->tgmpa->does_plugin_have_update( $slug );

				// Prep the upgrade notice info.
				$upgrade_notice = $this->tgmpa->get_upgrade_notice( $slug );
				if ( ! empty( $upgrade_notice ) ) {
					$table_data[ $i ]['upgrade_notice'] = $upgrade_notice;

					add_action( "tgmpa_after_plugin_row_{$slug}", array( $this, 'wp_plugin_update_row' ), 10, 2 );
				}

				$table_data[ $i ] = apply_filters( 'tgmpa_table_data_item', $table_data[ $i ], $plugin );

				$i++;
			}

			return $table_data;
		}

		/**
		 * Categorize the plugins which have open actions into views for the TGMPA page.
		 *
		 * @since 2.5.0
		 */
		protected function categorize_plugins_to_views() {
			$plugins = array(
				'all'      => array(), // Meaning: all plugins which still have open actions.
				'install'  => array(),
				'update'   => array(),
				'activate' => array(),
			);

			foreach ( $this->tgmpa->plugins as $slug => $plugin ) {
				if ( $this->tgmpa->is_plugin_active( $slug ) && false === $this->tgmpa->does_plugin_have_update( $slug ) ) {
					// No need to display plugins if they are installed, up-to-date and active.
					continue;
				} else {
					$plugins['all'][ $slug ] = $plugin;

					if ( ! $this->tgmpa->is_plugin_installed( $slug ) ) {
						$plugins['install'][ $slug ] = $plugin;
					} else {
						if ( false !== $this->tgmpa->does_plugin_have_update( $slug ) ) {
							$plugins['update'][ $slug ] = $plugin;
						}

						if ( $this->tgmpa->can_plugin_activate( $slug ) ) {
							$plugins['activate'][ $slug ] = $plugin;
						}
					}
				}
			}

			return $plugins;
		}

		/**
		 * Set the counts for the view links.
		 *
		 * @since 2.5.0
		 *
		 * @param array $plugins Plugins order by view.
		 */
		protected function set_view_totals( $plugins ) {
			foreach ( $plugins as $type => $list ) {
				$this->view_totals[ $type ] = count( $list );
			}
		}

		/**
		 * Get the plugin required/recommended text string.
		 *
		 * @since 2.5.0
		 *
		 * @param string $required Plugin required setting.
		 * @return string
		 */
		protected function get_plugin_advise_type_text( $required ) {
			if ( true === $required ) {
				return __( 'Required', 'tgmpa' );
			}

			return __( 'Recommended', 'tgmpa' );
		}

		/**
		 * Get the plugin source type text string.
		 *
		 * @since 2.5.0
		 *
		 * @param string $type Plugin type.
		 * @return string
		 */
		protected function get_plugin_source_type_text( $type ) {
			$string = '';

			switch ( $type ) {
				case 'repo':
					$string = __( 'WordPress Repository', 'tgmpa' );
					break;
				case 'external':
					$string = __( 'External Source', 'tgmpa' );
					break;
				case 'bundled':
					$string = __( 'Pre-Packaged', 'tgmpa' );
					break;
			}

			return $string;
		}

		/**
		 * Determine the plugin status message.
		 *
		 * @since 2.5.0
		 *
		 * @param string $slug Plugin slug.
		 * @return string
		 */
		protected function get_plugin_status_text( $slug ) {
			if ( ! $this->tgmpa->is_plugin_installed( $slug ) ) {
				return __( 'Not Installed', 'tgmpa' );
			}

			if ( ! $this->tgmpa->is_plugin_active( $slug ) ) {
				$install_status = __( 'Installed But Not Activated', 'tgmpa' );
			} else {
				$install_status = __( 'Active', 'tgmpa' );
			}

			$update_status = '';

			if ( $this->tgmpa->does_plugin_require_update( $slug ) && false === $this->tgmpa->does_plugin_have_update( $slug ) ) {
				$update_status = __( 'Required Update not Available', 'tgmpa' );

			} elseif ( $this->tgmpa->does_plugin_require_update( $slug ) ) {
				$update_status = __( 'Requires Update', 'tgmpa' );

			} elseif ( false !== $this->tgmpa->does_plugin_have_update( $slug ) ) {
				$update_status = __( 'Update recommended', 'tgmpa' );
			}

			if ( '' === $update_status ) {
				return $install_status;
			}

			return sprintf(
				/* translators: 1: install status, 2: update status */
				_x( '%1$s, %2$s', 'Install/Update Status', 'tgmpa' ),
				$install_status,
				$update_status
			);
		}

		/**
		 * Sort plugins by Required/Recommended type and by alphabetical plugin name within each type.
		 *
		 * @since 2.5.0
		 *
		 * @param array $items Prepared table items.
		 * @return array Sorted table items.
		 */
		public function sort_table_items( $items ) {
			$type = array();
			$name = array();

			foreach ( $items as $i => $plugin ) {
				$type[ $i ] = $plugin['type']; // Required / recommended.
				$name[ $i ] = $plugin['sanitized_plugin'];
			}

			array_multisort( $type, SORT_DESC, $name, SORT_ASC, $items );

			return $items;
		}

		/**
		 * Get an associative array ( id => link ) of the views available on this table.
		 *
		 * @since 2.5.0
		 *
		 * @return array
		 */
		public function get_views() {
			$status_links = array();

			foreach ( $this->view_totals as $type => $count ) {
				if ( $count < 1 ) {
					continue;
				}

				switch ( $type ) {
					case 'all':
						/* translators: 1: number of plugins. */
						$text = _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $count, 'plugins', 'tgmpa' );
						break;
					case 'install':
						/* translators: 1: number of plugins. */
						$text = _n( 'To Install <span class="count">(%s)</span>', 'To Install <span class="count">(%s)</span>', $count, 'tgmpa' );
						break;
					case 'update':
						/* translators: 1: number of plugins. */
						$text = _n( 'Update Available <span class="count">(%s)</span>', 'Update Available <span class="count">(%s)</span>', $count, 'tgmpa' );
						break;
					case 'activate':
						/* translators: 1: number of plugins. */
						$text = _n( 'To Activate <span class="count">(%s)</span>', 'To Activate <span class="count">(%s)</span>', $count, 'tgmpa' );
						break;
					default:
						$text = '';
						break;
				}

				if ( ! empty( $text ) ) {

					$status_links[ $type ] = sprintf(
						'<a href="%s"%s>%s</a>',
						esc_url( $this->tgmpa->get_tgmpa_status_url( $type ) ),
						( $type === $this->view_context ) ? ' class="current"' : '',
						sprintf( $text, number_format_i18n( $count ) )
					);
				}
			}

			return $status_links;
		}

		/**
		 * Create default columns to display important plugin information
		 * like type, action and status.
		 *
		 * @since 2.2.0
		 *
		 * @param array  $item        Array of item data.
		 * @param string $column_name The name of the column.
		 * @return string
		 */
		public function column_default( $item, $column_name ) {
			return $item[ $column_name ];
		}

		/**
		 * Required for bulk installing.
		 *
		 * Adds a checkbox for each plugin.
		 *
		 * @since 2.2.0
		 *
		 * @param array $item Array of item data.
		 * @return string The input checkbox with all necessary info.
		 */
		public function column_cb( $item ) {
			return sprintf(
				'<input type="checkbox" name="%1$s[]" value="%2$s" id="%3$s" />',
				esc_attr( $this->_args['singular'] ),
				esc_attr( $item['slug'] ),
				esc_attr( $item['sanitized_plugin'] )
			);
		}

		/**
		 * Create default title column along with the action links.
		 *
		 * @since 2.2.0
		 *
		 * @param array $item Array of item data.
		 * @return string The plugin name and action links.
		 */
		public function column_plugin( $item ) {
			return sprintf(
				'%1$s %2$s',
				$item['plugin'],
				$this->row_actions( $this->get_row_actions( $item ), true )
			);
		}

		/**
		 * Create version information column.
		 *
		 * @since 2.5.0
		 *
		 * @param array $item Array of item data.
		 * @return string HTML-formatted version information.
		 */
		public function column_version( $item ) {
			$output = array();

			if ( $this->tgmpa->is_plugin_installed( $item['slug'] ) ) {
				$installed = ! empty( $item['installed_version'] ) ? $item['installed_version'] : _x( 'unknown', 'as in: "version nr unknown"', 'tgmpa' );

				$color = '';
				if ( ! empty( $item['minimum_version'] ) && $this->tgmpa->does_plugin_require_update( $item['slug'] ) ) {
					$color = ' color: #ff0000; font-weight: bold;';
				}

				$output[] = sprintf(
					'<p><span style="min-width: 32px; text-align: right; float: right;%1$s">%2$s</span>' . __( 'Installed version:', 'tgmpa' ) . '</p>',
					$color,
					$installed
				);
			}

			if ( ! empty( $item['minimum_version'] ) ) {
				$output[] = sprintf(
					'<p><span style="min-width: 32px; text-align: right; float: right;">%1$s</span>' . __( 'Minimum required version:', 'tgmpa' ) . '</p>',
					$item['minimum_version']
				);
			}

			if ( ! empty( $item['available_version'] ) ) {
				$color = '';
				if ( ! empty( $item['minimum_version'] ) && version_compare( $item['available_version'], $item['minimum_version'], '>=' ) ) {
					$color = ' color: #71C671; font-weight: bold;';
				}

				$output[] = sprintf(
					'<p><span style="min-width: 32px; text-align: right; float: right;%1$s">%2$s</span>' . __( 'Available version:', 'tgmpa' ) . '</p>',
					$color,
					$item['available_version']
				);
			}

			if ( empty( $output ) ) {
				return '&nbsp;'; // Let's not break the table layout.
			} else {
				return implode( "\n", $output );
			}
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
			echo esc_html__( 'No plugins to install, update or activate.', 'tgmpa' ) . ' <a href="' . esc_url( self_admin_url() ) . '"> ' . esc_html( $this->tgmpa->strings['dashboard'] ) . '</a>';
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
			);

			if ( 'all' === $this->view_context || 'update' === $this->view_context ) {
				$columns['version'] = __( 'Version', 'tgmpa' );
				$columns['status']  = __( 'Status', 'tgmpa' );
			}

			return apply_filters( 'tgmpa_table_columns', $columns );
		}

		/**
		 * Get name of default primary column
		 *
		 * @since 2.5.0 / WP 4.3+ compatibility
		 * @access protected
		 *
		 * @return string
		 */
		protected function get_default_primary_column_name() {
			return 'plugin';
		}

		/**
		 * Get the name of the primary column.
		 *
		 * @since 2.5.0 / WP 4.3+ compatibility
		 * @access protected
		 *
		 * @return string The name of the primary column.
		 */
		protected function get_primary_column_name() {
			if ( method_exists( 'WP_List_Table', 'get_primary_column_name' ) ) {
				return parent::get_primary_column_name();
			} else {
				return $this->get_default_primary_column_name();
			}
		}

		/**
		 * Get the actions which are relevant for a specific plugin row.
		 *
		 * @since 2.5.0
		 *
		 * @param array $item Array of item data.
		 * @return array Array with relevant action links.
		 */
		protected function get_row_actions( $item ) {
			$actions      = array();
			$action_links = array();

			// Display the 'Install' action link if the plugin is not yet available.
			if ( ! $this->tgmpa->is_plugin_installed( $item['slug'] ) ) {
				/* translators: %2$s: plugin name in screen reader markup */
				$actions['install'] = __( 'Install %2$s', 'tgmpa' );
			} else {
				// Display the 'Update' action link if an update is available and WP complies with plugin minimum.
				if ( false !== $this->tgmpa->does_plugin_have_update( $item['slug'] ) && $this->tgmpa->can_plugin_update( $item['slug'] ) ) {
					/* translators: %2$s: plugin name in screen reader markup */
					$actions['update'] = __( 'Update %2$s', 'tgmpa' );
				}

				// Display the 'Activate' action link, but only if the plugin meets the minimum version.
				if ( $this->tgmpa->can_plugin_activate( $item['slug'] ) ) {
					/* translators: %2$s: plugin name in screen reader markup */
					$actions['activate'] = __( 'Activate %2$s', 'tgmpa' );
				}
			}

			// Create the actual links.
			foreach ( $actions as $action => $text ) {
				$nonce_url = wp_nonce_url(
					add_query_arg(
						array(
							'plugin'           => urlencode( $item['slug'] ),
							'tgmpa-' . $action => $action . '-plugin',
						),
						$this->tgmpa->get_tgmpa_url()
					),
					'tgmpa-' . $action,
					'tgmpa-nonce'
				);

				$action_links[ $action ] = sprintf(
					'<a href="%1$s">' . esc_html( $text ) . '</a>', // $text contains the second placeholder.
					esc_url( $nonce_url ),
					'<span class="screen-reader-text">' . esc_html( $item['sanitized_plugin'] ) . '</span>'
				);
			}

			$prefix = ( defined( 'WP_NETWORK_ADMIN' ) && WP_NETWORK_ADMIN ) ? 'network_admin_' : '';
			return apply_filters( "tgmpa_{$prefix}plugin_action_links", array_filter( $action_links ), $item['slug'], $item, $this->view_context );
		}

		/**
		 * Generates content for a single row of the table.
		 *
		 * @since 2.5.0
		 *
		 * @param object $item The current item.
		 */
		public function single_row( $item ) {
			echo '<tr class="' . esc_attr( 'tgmpa-type-' . strtolower( $item['type'] ) ) . '">';
			$this->single_row_columns( $item );
			echo '</tr>';

			/**
			 * Fires after each specific row in the TGMPA Plugins list table.
			 *
			 * The dynamic portion of the hook name, `$item['slug']`, refers to the slug
			 * for the plugin.
			 *
			 * @since 2.5.0
			 */
			do_action( "tgmpa_after_plugin_row_{$item['slug']}", $item['slug'], $item, $this->view_context );
		}

		/**
		 * Show the upgrade notice below a plugin row if there is one.
		 *
		 * @since 2.5.0
		 *
		 * @see /wp-admin/includes/update.php
		 *
		 * @param string $slug Plugin slug.
		 * @param array  $item The information available in this table row.
		 * @return null Return early if upgrade notice is empty.
		 */
		public function wp_plugin_update_row( $slug, $item ) {
			if ( empty( $item['upgrade_notice'] ) ) {
				return;
			}

			echo '
				<tr class="plugin-update-tr">
					<td colspan="', absint( $this->get_column_count() ), '" class="plugin-update colspanchange">
						<div class="update-message">',
							esc_html__( 'Upgrade message from the plugin author:', 'tgmpa' ),
							' <strong>', wp_kses_data( $item['upgrade_notice'] ), '</strong>
						</div>
					</td>
				</tr>';
		}

		/**
		 * Extra controls to be displayed between bulk actions and pagination.
		 *
		 * @since 2.5.0
		 *
		 * @param string $which 'top' or 'bottom' table navigation.
		 */
		public function extra_tablenav( $which ) {
			if ( 'bottom' === $which ) {
				$this->tgmpa->show_tgmpa_version();
			}
		}

		/**
		 * Defines the bulk actions for handling registered plugins.
		 *
		 * @since 2.2.0
		 *
		 * @return array $actions The bulk actions for the plugin install table.
		 */
		public function get_bulk_actions() {

			$actions = array();

			if ( 'update' !== $this->view_context && 'activate' !== $this->view_context ) {
				if ( current_user_can( 'install_plugins' ) ) {
					$actions['tgmpa-bulk-install'] = __( 'Install', 'tgmpa' );
				}
			}

			if ( 'install' !== $this->view_context ) {
				if ( current_user_can( 'update_plugins' ) ) {
					$actions['tgmpa-bulk-update'] = __( 'Update', 'tgmpa' );
				}
				if ( current_user_can( 'activate_plugins' ) ) {
					$actions['tgmpa-bulk-activate'] = __( 'Activate', 'tgmpa' );
				}
			}

			return $actions;
		}

		/**
		 * Processes bulk installation and activation actions.
		 *
		 * The bulk installation process looks for the $_POST information and passes that
		 * through if a user has to use WP_Filesystem to enter their credentials.
		 *
		 * @since 2.2.0
		 */
		public function process_bulk_actions() {
			// Bulk installation process.
			if ( 'tgmpa-bulk-install' === $this->current_action() || 'tgmpa-bulk-update' === $this->current_action() ) {

				check_admin_referer( 'bulk-' . $this->_args['plural'] );

				$install_type = 'install';
				if ( 'tgmpa-bulk-update' === $this->current_action() ) {
					$install_type = 'update';
				}

				$plugins_to_install = array();

				// Did user actually select any plugins to install/update ?
				if ( empty( $_POST['plugin'] ) ) {
					if ( 'install' === $install_type ) {
						$message = __( 'No plugins were selected to be installed. No action taken.', 'tgmpa' );
					} else {
						$message = __( 'No plugins were selected to be updated. No action taken.', 'tgmpa' );
					}

					echo '<div id="message" class="error"><p>', esc_html( $message ), '</p></div>';

					return false;
				}

				if ( is_array( $_POST['plugin'] ) ) {
					$plugins_to_install = (array) $_POST['plugin'];
				} elseif ( is_string( $_POST['plugin'] ) ) {
					// Received via Filesystem page - un-flatten array (WP bug #19643).
					$plugins_to_install = explode( ',', $_POST['plugin'] );
				}

				// Sanitize the received input.
				$plugins_to_install = array_map( 'urldecode', $plugins_to_install );
				$plugins_to_install = array_map( array( $this->tgmpa, 'sanitize_key' ), $plugins_to_install );

				// Validate the received input.
				foreach ( $plugins_to_install as $key => $slug ) {
					// Check if the plugin was registered with TGMPA and remove if not.
					if ( ! isset( $this->tgmpa->plugins[ $slug ] ) ) {
						unset( $plugins_to_install[ $key ] );
						continue;
					}

					// For install: make sure this is a plugin we *can* install and not one already installed.
					if ( 'install' === $install_type && true === $this->tgmpa->is_plugin_installed( $slug ) ) {
						unset( $plugins_to_install[ $key ] );
					}

					// For updates: make sure this is a plugin we *can* update (update available and WP version ok).
					if ( 'update' === $install_type && false === $this->tgmpa->is_plugin_updatetable( $slug ) ) {
						unset( $plugins_to_install[ $key ] );
					}
				}

				// No need to proceed further if we have no plugins to handle.
				if ( empty( $plugins_to_install ) ) {
					if ( 'install' === $install_type ) {
						$message = __( 'No plugins are available to be installed at this time.', 'tgmpa' );
					} else {
						$message = __( 'No plugins are available to be updated at this time.', 'tgmpa' );
					}

					echo '<div id="message" class="error"><p>', esc_html( $message ), '</p></div>';

					return false;
				}

				// Pass all necessary information if WP_Filesystem is needed.
				$url = wp_nonce_url(
					$this->tgmpa->get_tgmpa_url(),
					'bulk-' . $this->_args['plural']
				);

				// Give validated data back to $_POST which is the only place the filesystem looks for extra fields.
				$_POST['plugin'] = implode( ',', $plugins_to_install ); // Work around for WP bug #19643.

				$method = ''; // Leave blank so WP_Filesystem can populate it as necessary.
				$fields = array_keys( $_POST ); // Extra fields to pass to WP_Filesystem.

				$creds = request_filesystem_credentials( esc_url_raw( $url ), $method, false, false, $fields );
				if ( false === $creds ) {
					return true; // Stop the normal page form from displaying, credential request form will be shown.
				}

				// Now we have some credentials, setup WP_Filesystem.
				if ( ! WP_Filesystem( $creds ) ) {
					// Our credentials were no good, ask the user for them again.
					request_filesystem_credentials( esc_url_raw( $url ), $method, true, false, $fields );

					return true;
				}

				/* If we arrive here, we have the filesystem */

				// Store all information in arrays since we are processing a bulk installation.
				$names      = array();
				$sources    = array(); // Needed for installs.
				$file_paths = array(); // Needed for upgrades.
				$to_inject  = array(); // Information to inject into the update_plugins transient.

				// Prepare the data for validated plugins for the install/upgrade.
				foreach ( $plugins_to_install as $slug ) {
					$name   = $this->tgmpa->plugins[ $slug ]['name'];
					$source = $this->tgmpa->get_download_url( $slug );

					if ( ! empty( $name ) && ! empty( $source ) ) {
						$names[] = $name;

						switch ( $install_type ) {

							case 'install':
								$sources[] = $source;
								break;

							case 'update':
								$file_paths[]                 = $this->tgmpa->plugins[ $slug ]['file_path'];
								$to_inject[ $slug ]           = $this->tgmpa->plugins[ $slug ];
								$to_inject[ $slug ]['source'] = $source;
								break;
						}
					}
				}
				unset( $slug, $name, $source );
				
				if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
					require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
				}

				// Create a new instance of TGMPA_Bulk_Installer.
				$installer = new TGMPA_Bulk_Installer(
					new TGMPA_Bulk_Installer_Skin(
						array(
							'url'          => esc_url_raw( $this->tgmpa->get_tgmpa_url() ),
							'nonce'        => 'bulk-' . $this->_args['plural'],
							'names'        => $names,
							'install_type' => $install_type,
						)
					)
				);

				// Wrap the install process with the appropriate HTML.
				echo '<div class="tgmpa">',
					'<h2 style="font-size: 23px; font-weight: 400; line-height: 29px; margin: 0; padding: 9px 15px 4px 0;">', esc_html( get_admin_page_title() ), '</h2>
					<div class="update-php" style="width: 100%; height: 98%; min-height: 850px; padding-top: 1px;">';

				// Process the bulk installation submissions.
				add_filter( 'upgrader_source_selection', array( $this->tgmpa, 'maybe_adjust_source_dir' ), 1, 3 );

				if ( 'tgmpa-bulk-update' === $this->current_action() ) {
					// Inject our info into the update transient.
					$this->tgmpa->inject_update_info( $to_inject );

					$installer->bulk_upgrade( $file_paths );
				} else {
					$installer->bulk_install( $sources );
				}

				remove_filter( 'upgrader_source_selection', array( $this->tgmpa, 'maybe_adjust_source_dir' ), 1 );

				echo '</div></div>';

				return true;
			}

			// Bulk activation process.
			if ( 'tgmpa-bulk-activate' === $this->current_action() ) {
				check_admin_referer( 'bulk-' . $this->_args['plural'] );

				// Did user actually select any plugins to activate ?
				if ( empty( $_POST['plugin'] ) ) {
					echo '<div id="message" class="error"><p>', esc_html__( 'No plugins were selected to be activated. No action taken.', 'tgmpa' ), '</p></div>';

					return false;
				}

				// Grab plugin data from $_POST.
				$plugins = array();
				if ( isset( $_POST['plugin'] ) ) {
					$plugins = array_map( 'urldecode', (array) $_POST['plugin'] );
					$plugins = array_map( array( $this->tgmpa, 'sanitize_key' ), $plugins );
				}

				$plugins_to_activate = array();
				$plugin_names        = array();

				// Grab the file paths for the selected & inactive plugins from the registration array.
				foreach ( $plugins as $slug ) {
					if ( $this->tgmpa->can_plugin_activate( $slug ) ) {
						$plugins_to_activate[] = $this->tgmpa->plugins[ $slug ]['file_path'];
						$plugin_names[]        = $this->tgmpa->plugins[ $slug ]['name'];
					}
				}
				unset( $slug );

				// Return early if there are no plugins to activate.
				if ( empty( $plugins_to_activate ) ) {
					echo '<div id="message" class="error"><p>', esc_html__( 'No plugins are available to be activated at this time.', 'tgmpa' ), '</p></div>';

					return false;
				}

				// Now we are good to go - let's start activating plugins.
				$activate = activate_plugins( $plugins_to_activate );

				if ( is_wp_error( $activate ) ) {
					echo '<div id="message" class="error"><p>', wp_kses_post( $activate->get_error_message() ), '</p></div>';
				} else {
					$count        = count( $plugin_names ); // Count so we can use _n function.
					$plugin_names = array_map( array( TGMPA_Utils::class, 'wrap_in_strong' ), $plugin_names );
					$last_plugin  = array_pop( $plugin_names ); // Pop off last name to prep for readability.
					$imploded     = empty( $plugin_names ) ? $last_plugin : ( implode( ', ', $plugin_names ) . ' ' . esc_html_x( 'and', 'plugin A *and* plugin B', 'tgmpa' ) . ' ' . $last_plugin );

					printf( // WPCS: xss ok.
						'<div id="message" class="updated"><p>%1$s %2$s.</p></div>',
						esc_html( _n( 'The following plugin was activated successfully:', 'The following plugins were activated successfully:', $count, 'tgmpa' ) ),
						$imploded
					);

					// Update recently activated plugins option.
					$recent = (array) get_option( 'recently_activated' );
					foreach ( $plugins_to_activate as $plugin => $time ) {
						if ( isset( $recent[ $plugin ] ) ) {
							unset( $recent[ $plugin ] );
						}
					}
					update_option( 'recently_activated', $recent );
				}

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
			$primary               = $this->get_primary_column_name(); // Column which has the row actions.
			$this->_column_headers = array( $columns, $hidden, $sortable, $primary ); // Get all necessary column headers.

			// Process our bulk activations here.
			if ( 'tgmpa-bulk-activate' === $this->current_action() ) {
				$this->process_bulk_actions();
			}

			// Store all of our plugin data into $items array so WP_List_Table can use it.
			$this->items = apply_filters( 'tgmpa_table_data_items', $this->_gather_plugin_data() );
		}

		/* *********** DEPRECATED METHODS *********** */

		/**
		 * Retrieve plugin data, given the plugin name.
		 *
		 * @since      2.2.0
		 * @deprecated 2.5.0 use {@see TGM_Plugin_Activation::_get_plugin_data_from_name()} instead.
		 * @see        TGM_Plugin_Activation::_get_plugin_data_from_name()
		 *
		 * @param string $name Name of the plugin, as it was registered.
		 * @param string $data Optional. Array key of plugin data to return. Default is slug.
		 * @return string|boolean Plugin slug if found, false otherwise.
		 */
		protected function _get_plugin_data_from_name( $name, $data = 'slug' ) {
			_deprecated_function( __FUNCTION__, 'TGMPA 2.5.0', 'TGM_Plugin_Activation::_get_plugin_data_from_name()' );

			return $this->tgmpa->_get_plugin_data_from_name( $name, $data );
		}
	}
