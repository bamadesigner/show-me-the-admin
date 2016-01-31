<?php

class Show_Me_The_Admin_Admin {

	/**
	 * Is true when multisite
	 * and in the network admin
	 *
	 * @since 1.0.0
	 * @access public
	 * @var boolean
	 */
	public $is_network_admin;

	/**
	 * Holds the URL for the
	 * settings page
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	public $settings_page_url;

	/**
	 * ID of the regular settings page
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	public $settings_page_id;

	/**
	 * Takes care of admin shenanigans.
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function __construct() {

		// Lets us know if we're dealing with a multisite and in the network admin
		if ( is_multisite() && is_network_admin() ) {

			// We're in the network admin
			$this->is_network_admin = true;

			// Define the settings page URL
			$this->settings_page_url = add_query_arg( array( 'page' => 'show-me-the-admin' ), network_admin_url( 'settings.php' ) );

		}

		// We're not in the network admin
		else {

			// We're not in the network admin
			$this->is_network_admin = false;

			// Define the settings page URL
			$this->settings_page_url = add_query_arg( array( 'page' => 'show-me-the-admin' ), admin_url( 'options-general.php' ) );

		}

		// Add plugin action links
		add_filter( 'network_admin_plugin_action_links_' . SHOW_ME_THE_ADMIN_PLUGIN_FILE, array( $this, 'add_plugin_action_links' ), 10, 4 );
		add_filter( 'plugin_action_links_' . SHOW_ME_THE_ADMIN_PLUGIN_FILE, array( $this, 'add_plugin_action_links' ), 10, 4 );

		// Add multisite settings page
		add_action( 'network_admin_menu', array( $this, 'add_network_settings_page' ) );

		// Add regular settings page
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );

		// Add our settings meta boxes
		add_action( 'admin_head-settings_page_show-me-the-admin', array( $this, 'add_settings_meta_boxes' ) );

		// Add styles and scripts for the settings page
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ) );

		// Update multisite settings
		add_action( 'update_wpmu_options', array( $this, 'update_network_settings' ) );

		// Register our settings
		add_action( 'admin_init', array( $this, 'register_settings' ) );

	}

	/**
	 * Add our own plugin action links.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param   array - $actions - An array of plugin action links
	 * @param   string - $$plugin_file - Path to the plugin file
	 * @param   array - $plugin_data - An array of plugin data
	 * @param   string - $context - The plugin context. Defaults are 'All', 'Active',
	 *                      'Inactive', 'Recently Activated', 'Upgrade',
	 *                      'Must-Use', 'Drop-ins', 'Search'.
	 * @return  array - the filtered actions
	 */
	public function add_plugin_action_links( $actions, $plugin_file, $plugin_data, $context ) {
		if ( $this->is_network_admin ? current_user_can( 'manage_network_options' ) : current_user_can( 'manage_options' ) ) {
			$actions[] = '<a href="' . $this->settings_page_url . '">' . __( 'Manage', 'show-me-the-admin' ) . '</a>';
		}
		return $actions;
	}

	/**
	 * Add our network Settings page.
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function add_network_settings_page() {

		// Make sure plugin is network activated
		if ( ! ( function_exists( 'is_plugin_active_for_network' ) && is_plugin_active_for_network( SHOW_ME_THE_ADMIN_PLUGIN_FILE ) ) ) {
			return;
		}

		// Add the network settings page
		$this->settings_page_id = add_submenu_page( 'settings.php', __( 'Show Me The Admin', 'show-me-the-admin' ), __( 'Show Me The Admin', 'show-me-the-admin' ), 'manage_network_options', 'show-me-the-admin', array( $this, 'print_settings_page' ) );

	}

	/**
	 * Add our regular settings page.
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function add_settings_page() {
		$this->settings_page_id = add_options_page( __( 'Show Me The Admin', 'show-me-the-admin' ), __( 'Show Me The Admin', 'show-me-the-admin' ), 'manage_options', 'show-me-the-admin', array( $this, 'print_settings_page' ) );
	}

	/**
	 * Add styles and scripts for our settings page.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param	string - $hook_suffix - the ID of the current page
	 */
	public function enqueue_styles_scripts( $hook_suffix ) {

		// Only for our settings pages
		if ( $hook_suffix != $this->settings_page_id ) {
			return;
		}

		// Enqueue our main styles
		wp_enqueue_style( 'show-me-the-admin-settings', trailingslashit( plugin_dir_url( dirname( __FILE__ ) ) . 'css' ) . 'admin-settings.min.css', array(), SHOW_ME_THE_ADMIN_VERSION );

		// Need this script for the meta boxes to work correctly
		wp_enqueue_script( 'post' );
		wp_enqueue_script( 'postbox' );

	}

	/**
	 * Add our settings meta boxes.
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function add_settings_meta_boxes() {

		// About this Plugin
		add_meta_box( 'show-me-the-admin-about-mb', __( 'About this Plugin', 'show-me-the-admin' ), array( $this, 'print_settings_meta_boxes' ), $this->settings_page_id, 'side', 'core', 'about-plugin' );

		// Spread the Love
		add_meta_box( 'show-me-the-admin-promote-mb', __( 'Spread the Love', 'show-me-the-admin' ), array( $this, 'print_settings_meta_boxes' ), $this->settings_page_id, 'side', 'core', 'promote' );

		// The Settings
		add_meta_box( 'show-me-the-admin-settings-mb', __( 'The Settings', 'show-me-the-admin' ), array( $this, 'print_settings_meta_boxes' ), $this->settings_page_id, 'normal', 'core', 'the-settings' );

	}

	/**
	 * Print our settings meta boxes.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param 	array - $post - information about the current post, which is empty because there is no current post on a settings page
	 * @param 	array - $metabox - information about the metabox
	 */
	public function print_settings_meta_boxes( $post, $metabox ) {

		switch( $metabox[ 'args' ] ) {

			// About meta box
			case 'about-plugin':
				?><p><?php _e( 'Hides your admin toolbar and enables you to make it appear, and disappear, by typing a specific phrase.', 'show-me-the-admin' ); ?></p>
				<p><strong><a href="<?php echo SHOW_ME_THE_ADMIN_PLUGIN_URL; ?>" target="_blank"><?php _e( 'Show Me The Admin', 'show-me-the-admin' ); ?></a></strong><br />
				<strong><?php _e( 'Version', 'show-me-the-admin' ); ?>:</strong> <?php echo SHOW_ME_THE_ADMIN_VERSION; ?><br /><strong><?php _e( 'Author', 'show-me-the-admin' ); ?>:</strong> <a href="http://bamadesigner.com/" target="_blank">Rachel Carden</a></p><?php
				break;

			// Promote meta box
			case 'promote':
				?><p class="star"><a href="<?php echo SHOW_ME_THE_ADMIN_PLUGIN_URL; ?>" title="<?php esc_attr_e( 'Give the plugin a good rating', 'show-me-the-admin' ); ?>" target="_blank"><span class="dashicons dashicons-star-filled"></span> <span class="promote-text"><?php _e( 'Give the plugin a good rating', 'show-me-the-admin' ); ?></span></a></p>
				<p class="twitter"><a href="https://twitter.com/bamadesigner" title="<?php _e( 'Follow bamadesigner on Twitter', 'show-me-the-admin' ); ?>" target="_blank"><span class="dashicons dashicons-twitter"></span> <span class="promote-text"><?php _e( 'Follow me on Twitter', 'show-me-the-admin' ); ?></span></a></p>
				<p class="donate"><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ZCAN2UX7QHZPL&lc=US&item_name=Rachel%20Carden%20%28Show%20Me%20The%20Admin%29&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted" title="<?php esc_attr_e( 'Donate a few bucks to the plugin', 'show-me-the-admin' ); ?>" target="_blank"><img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" alt="<?php esc_attr_e( 'Donate', 'show-me-the-admin' ); ?>" /> <span class="promote-text"><?php _e( 'and buy me a coffee', 'show-me-the-admin' ); ?></span></a></p><?php
				break;

			case 'the-settings':

				// Get our settings
				$settings = $this->get_settings( $this->is_network_admin );

				// Print the settings table
				?><table id="show-me-the-admin-settings" class="form-table">
					<tbody>
						<tr>
							<td>
								<label for=""><strong><?php _e( 'Phrase to "show" the admin bar', 'show-me-the-admin' ); ?></strong></label>
								<input name="show_me_the_admin[show_phrase]" type="text" id="" value="<?php esc_attr_e( isset( $settings[ 'show_phrase' ] ) ? $settings[ 'show_phrase' ] : null ); ?>" class="regular-text" />
								<p class="description" id="tagline-description"><?php _e( 'The default phrase is "showme".', 'show-me-the-admin' ); ?></p>
							</td>
						</tr>
						<tr>
							<td>
								<label for=""><strong><?php _e( 'Phrase to "hide" the admin bar', 'show-me-the-admin' ); ?></strong></label>
								<input name="show_me_the_admin[hide_phrase]" type="text" id="" value="<?php esc_attr_e( isset( $settings[ 'hide_phrase' ] ) ? $settings[ 'hide_phrase' ] : null ); ?>" class="regular-text" />
								<p class="description" id="tagline-description"><?php _e( 'The default phrase is "hideme".', 'show-me-the-admin' ); ?></p>
							</td>
						</tr>
					</tbody>
				</table><?php
				break;

		}

	}


	/**
	 * Prints our settings page.
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function print_settings_page() {

		?><div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1><?php

			// Need this to show errors in network admin
			if ( $this->is_network_admin ) {
				settings_errors( 'show_me_the_admin' );
			}

			// Do we need the form?
			?><form method="post" action="<?php echo ( $this->is_network_admin ) ? 'settings.php' : 'options.php'; ?>" novalidate="novalidate"><?php

				// Handle network settings
				if ( $this->is_network_admin ) {
					wp_nonce_field( 'siteoptions' );
				}

				// Handle non-network settings
				else {
					settings_fields( 'show_me_the_admin' );
				}

				?><div id="poststuff">
					<div id="post-body" class="metabox-holder columns-2">

						<div id="postbox-container-1" class="postbox-container">

							<div id="side-sortables" class="meta-box-sortables"><?php
								do_meta_boxes( $this->settings_page_id, 'side', array() );
							?></div> <!-- #side-sortables -->

						</div> <!-- #postbox-container-1 -->

						<div id="postbox-container-2" class="postbox-container">

							<div id="normal-sortables" class="meta-box-sortables"><?php
								do_meta_boxes( $this->settings_page_id, 'normal', array() );
							?></div> <!-- #normal-sortables -->

							<div id="advanced-sortables" class="meta-box-sortables"><?php
								do_meta_boxes( $this->settings_page_id, 'advanced', array() );
							?></div> <!-- #advanced-sortables --><?php

							submit_button( 'Save Changes', 'primary', 'show_me_the_admin_save_changes', false );

						?></div> <!-- #postbox-container-2 -->

					</div> <!-- #post-body -->
					<br class="clear" />
				</div> <!-- #poststuff -->
			</form>
		</div> <!-- .wrap --><?php

	}

	/**
	 * Register our settings.
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function register_settings() {
		register_setting( 'show_me_the_admin', 'show_me_the_admin', array( $this, 'update_settings' ) );
	}

	/**
	 * Returns our straight-forward, not adjusted settings.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param	boolean - $network - whether or not to retrieve network settings
	 * @return  array - the settings
	 */
	private function get_settings( $network = false ) {

		// What are the default settings?
		$defaults = show_me_the_admin()->get_default_settings();

		return $network ? get_site_option( 'show_me_the_admin', $defaults ) : get_option( 'show_me_the_admin', $defaults );
	}

	/**
	 * Validates/updates our network setting.
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function update_network_settings() {

		// Makes sure we're saving in the network admin
		if ( current_user_can( 'manage_network_options' )
			&& check_admin_referer( 'siteoptions' )
			&& isset( $_POST[ 'show_me_the_admin_save_changes' ] ) ) {

			// Get/update/validate the settings
			if ( isset( $_POST[ 'show_me_the_admin' ] )
				&& ( $settings = $_POST[ 'show_me_the_admin' ] ) ) {

				// Validate the settings
				$settings = $this->validate_settings( $settings );

				// Update settings
				update_site_option( 'show_me_the_admin', $settings );

				// If no errors, then show general message
				add_settings_error( 'show_me_the_admin', 'settings_updated', __( 'Settings saved.', 'show-me-the-admin' ), 'updated' );

				// Stores any settings errors so they can be displayed on redirect
				set_transient( 'settings_errors', get_settings_errors(), 30 );

				// Redirect to settings page
				wp_redirect( add_query_arg( array( 'settings-updated' => 'true' ), $_REQUEST[ '_wp_http_referer' ] ) );
				exit();

			}

		}

	}

	/**
	 * Updates the 'show_me_the_admin' setting.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param	array - the settings we're sanitizing
	 * @return	array - the updated settings
	 */
	public function update_settings( $settings ) {

		// Validate the settings
		$settings = $this->validate_settings( $settings );

		// Return the validated settings
		return $settings;

	}

	/**
	 * Validates our settings.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param	array - the settings being validated
	 * @return	array - the validated settings
	 */
	public function validate_settings( $settings ) {
		return $settings;
	}

}
new Show_Me_The_Admin_Admin;