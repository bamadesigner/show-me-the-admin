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

		// Add user profile settings
		add_action( 'profile_personal_options', array( $this, 'add_user_profile_settings' ), 0 );

		// Save user profile settings
		add_action( 'personal_options_update', array( $this, 'save_user_profile_settings' ), 0 );
		add_action( 'edit_user_profile_update', array( $this, 'save_user_profile_settings' ), 0 );

		// Add any admin notices
		add_action( 'admin_notices', array( $this, 'print_user_admin_notice' ) );

		// Runs an ajax call to add the users setting and user notice
		add_action( 'wp_ajax_smta_add_users_setting_notice', array( $this, 'add_users_setting_notice' ) );
		add_action( 'wp_ajax_smta_add_user_notice', array( $this, 'smta_add_user_notice' ) );

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
	 * @global	$smta_users_setting_notice - boolean on whether to include users setting notice
	 * @global	$smta_user_notice - boolean on whether to include user notice
	 */
	public function enqueue_styles_scripts( $hook_suffix ) {
		global $smta_users_setting_notice, $smta_user_notice;

		// We only need our styles for our settings pages and the user profile pages
		if ( in_array( $hook_suffix, array( $this->settings_page_id, 'profile.php' ) ) ) {

			// Enqueue our main styles
			wp_enqueue_style( 'show-me-the-admin-settings', trailingslashit( plugin_dir_url( dirname( __FILE__ ) ) . 'css' ) . 'admin-settings.min.css', array(), SHOW_ME_THE_ADMIN_VERSION );

			// Need this script for the meta boxes to work correctly on our settings page
			if ( $hook_suffix == $this->settings_page_id ) {
				wp_enqueue_script( 'post' );
				wp_enqueue_script( 'postbox' );
			}

		}

		// Determine whether to include our user notice scripts when needed (but not on the settings and profile page)
		else {

			// Don't show notices by default
			$smta_users_setting_notice = false;
			$smta_user_notice = false;

			// Will be true if we should enqueue our user notice script
			$enqueue_user_notice_script = false;

			// Only show user settings notice for the user who activated the plugin
			$activated_user = get_user_meta( get_current_user_id(), 'show_me_the_admin_activated_user', true );
			if ( $activated_user !== false && $activated_user > 0 ) {

				// Do we need to show the user settings notice?
				$users_setting_notice = get_user_meta( get_current_user_id(), 'show_me_the_admin_users_setting_notice', true );
				$show_users_setting_notice = ! ( $users_setting_notice !== false && $users_setting_notice > 0 && $users_setting_notice <= time() );

				// Include script for the users settings notice
				if ( $show_users_setting_notice ) {

					// Make sure we include the notice
					$smta_users_setting_notice = true;

					// Enqueue the script
					$enqueue_user_notice_script = true;

				}

			} // Show the user notice for everyone else
			else {

				// Get the settings
				$settings = $this->get_settings();

				// Only print if the notice is enabled
				if ( isset( $settings[ 'enable_user_notice' ] ) && $settings[ 'enable_user_notice' ] == true ) {

					// Do we need to include the actual user notice?
					$user_notice = get_user_meta( get_current_user_id(), 'show_me_the_admin_user_notice', true );

					// Include script for the users settings notice
					if ( ! ( $user_notice !== false && $user_notice > 0 && $user_notice <= time() ) ) {

						// Make sure we include the notice
						$smta_user_notice = true;

						// Enqueue the script
						$enqueue_user_notice_script = true;

					}

				}

			}

			// Enqueue our script
			if ( $enqueue_user_notice_script ) {
				wp_enqueue_script( 'show-me-the-admin-user-notice', trailingslashit( plugin_dir_url( dirname( __FILE__ ) ) . 'js' ) . 'show-me-the-admin-user-notice.min.js', array( 'jquery' ), SHOW_ME_THE_ADMIN_VERSION, true );
			}

		}

	}

	/**
	 * Add our settings meta boxes.
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function add_settings_meta_boxes() {

		// Get our settings
		$site_settings = $this->get_settings( $this->is_network_admin );

		// Set the default phrases
		$default_show_phrase = SHOW_ME_THE_ADMIN_SHOW_PHRASE;
		$default_hide_phrase = SHOW_ME_THE_ADMIN_HIDE_PHRASE;

		// If network active, get network settings for help with default phrases
		if ( show_me_the_admin()->is_network_active ) {

			// Get network settings
			$network_settings = $this->get_settings( true );

			// Set the default phrases
			$default_show_phrase = ! empty( $network_settings[ 'show_phrase' ] ) ? $network_settings[ 'show_phrase' ] : $default_show_phrase;
			$default_hide_phrase = ! empty( $network_settings[ 'hide_phrase' ] ) ? $network_settings[ 'hide_phrase' ] : $default_hide_phrase;

		}

		// About this Plugin
		add_meta_box( 'show-me-the-admin-about-mb', __( 'About this Plugin', 'show-me-the-admin' ), array( $this, 'print_settings_meta_boxes' ), $this->settings_page_id, 'side', 'core', array( 'id' => 'about-plugin' ) );

		// Spread the Love
		add_meta_box( 'show-me-the-admin-promote-mb', __( 'Spread the Love', 'show-me-the-admin' ), array( $this, 'print_settings_meta_boxes' ), $this->settings_page_id, 'side', 'core', array( 'id' => 'promote' ) );

		// The Users
		add_meta_box( 'show-me-the-admin-users-mb', __( 'The Users', 'show-me-the-admin' ), array( $this, 'print_settings_meta_boxes' ), $this->settings_page_id, 'normal', 'core', array( 'id' => 'the-users', 'site_settings' => $site_settings ) );

		// The Settings
		add_meta_box( 'show-me-the-admin-settings-mb', __( 'The Settings', 'show-me-the-admin' ), array( $this, 'print_settings_meta_boxes' ), $this->settings_page_id, 'normal', 'core', array( 'id' => 'the-settings', 'site_settings' => $site_settings, 'default_show_phrase' => $default_show_phrase, 'default_hide_phrase' => $default_hide_phrase ) );

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

		switch( $metabox[ 'args' ][ 'id' ] ) {

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

			// Users meta box
			case 'the-users':

				// Print the users settings table
				?><p style="margin-bottom:0;"><?php _e( 'Your users have the ability to customize, or even disable, this functionality by <a href="' . admin_url( 'profile.php#smta-user-profile-settings' ) . '">editing their user profile</a>.', 'show-me-the-admin' ); ?></p>
				<table id="show-me-the-admin-user-settings" class="form-table show-me-the-admin-settings">
					<tbody>
						<tr>
							<td>
								<fieldset>
									<legend class="screen-reader-text"><span><?php _e( 'Provide A User Notice', 'show-me-the-admin' ); ?></span></legend>
									<label for="smta-user-notice"><input name="show_me_the_admin[enable_user_notice]" type="checkbox" id="smta-user-notice" value="1"<?php checked( isset( $metabox[ 'args' ][ 'site_settings' ][ 'enable_user_notice' ] ) && $metabox[ 'args' ][ 'site_settings' ][ 'enable_user_notice' ] == true ) ?>/> <strong><?php _e( 'Provide an admin notice that will alert your users to this plugin\'s functionality', 'show-me-the-admin' ); ?></strong></label>
									<p class="description" id="tagline-description"><?php _e( 'Otherwise, I imagine the hidden admin bar might cause confusion.', 'show-me-the-admin' ); ?></p>
								</fieldset>
							</td>
						</tr>
					</tbody>
				</table><?php
				break;

			// Settings meta box
			case 'the-settings':

				// Print the settings table
				?><table id="show-me-the-admin-settings" class="form-table show-me-the-admin-settings">
					<tbody>
						<tr>
							<td>
								<label for="smta-show-phrase"><strong><?php _e( 'Phrase to "show" the admin bar', 'show-me-the-admin' ); ?></strong></label>
								<input name="show_me_the_admin[show_phrase]" type="text" id="smta-show-phrase" value="<?php esc_attr_e( isset( $metabox[ 'args' ][ 'site_settings' ][ 'show_phrase' ] ) ? $metabox[ 'args' ][ 'site_settings' ][ 'show_phrase' ] : null ); ?>" placeholder="<?php esc_attr_e( $metabox[ 'args' ][ 'default_show_phrase' ] ); ?>" class="regular-text" />
								<p class="description" id="tagline-description"><?php printf( __( 'If left blank, will use the default phrase "%s".', 'show-me-the-admin' ), $metabox[ 'args' ][ 'default_show_phrase' ] ); ?></p>
							</td>
						</tr>
						<tr>
							<td>
								<label for="smta-hide-phrase"><strong><?php _e( 'Phrase to "hide" the admin bar', 'show-me-the-admin' ); ?></strong></label>
								<input name="show_me_the_admin[hide_phrase]" type="text" id="smta-hide-phrase" value="<?php esc_attr_e( isset( $metabox[ 'args' ][ 'site_settings' ][ 'hide_phrase' ] ) ? $metabox[ 'args' ][ 'site_settings' ][ 'hide_phrase' ] : null ); ?>" placeholder="<?php esc_attr_e( $metabox[ 'args' ][ 'default_hide_phrase' ] ); ?>"class="regular-text" />
								<p class="description" id="tagline-description"><?php printf( __( 'If left blank, will use the default phrase "%s".', 'show-me-the-admin' ), $metabox[ 'args' ][ 'default_hide_phrase' ] ); ?></p>
							</td>
						</tr>
						<tr>
							<td>
								<fieldset>
									<legend class="screen-reader-text"><span><?php _e( 'Enable the Login Button', 'show-me-the-admin' ); ?></span></legend>
									<label for="smta-login-button"><input name="show_me_the_admin[enable_login_button]" type="checkbox" id="smta-login-button" value="1"<?php checked( isset( $metabox[ 'args' ][ 'site_settings' ][ 'enable_login_button' ] ) && $metabox[ 'args' ][ 'site_settings' ][ 'enable_login_button' ] == true ) ?>/> <strong><?php _e( 'If not logged in, show a login button instead of the admin bar', 'show-me-the-admin' ); ?></strong></label>
									<p class="description" id="tagline-description"><?php _e( 'If enabled, and not logged in, the "show" and "hide" phrase will reveal and hide a login button.', 'show-me-the-admin' ); ?></p>
								</fieldset>
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

			// Print the settings form
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
	 * @access  private
	 * @since   1.0.0
	 * @param	boolean - $network - whether or not to retrieve network settings
	 * @return  array - the settings
	 */
	private function get_settings( $network = false ) {

		// Get settings
		$settings = $network ? get_site_option( 'show_me_the_admin', array( 'enable_user_notice' => true, 'enable_login_button' => true ) ) : get_option( 'show_me_the_admin', array( 'enable_user_notice' => true, 'enable_login_button' => true ) );

		// Make sure its an array
		if ( empty( $settings ) ) {
			$settings = array();
		}

		return $settings;
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

	/**
	 * Adds custom user profile settings.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param	WP_User - $profile_user - the current WP_User object
	 */
	public function add_user_profile_settings( $profile_user ) {

		// Get the user settings
		$user_settings = show_me_the_admin()->get_user_settings( $profile_user->ID );

		// Get site settings in order to tell user the default phrases
		$site_settings = $this->get_settings();

		// If network active, merge site settings with network settings
		if ( show_me_the_admin()->is_network_active ) {

			// Get network settings
			$network_settings = $this->get_settings( true );

			// Remove empty values for merging
			$site_settings = array_filter( $site_settings );
			$network_settings = array_filter( $network_settings );

			// Merge site with network settings
			$site_settings = wp_parse_args( $site_settings, $network_settings );

		}

		// Set the default phrases
		$default_show_phrase = ! empty( $site_settings[ 'show_phrase' ] ) ? $site_settings[ 'show_phrase' ] : SHOW_ME_THE_ADMIN_SHOW_PHRASE;
		$default_hide_phrase = ! empty( $site_settings[ 'hide_phrase' ] ) ? $site_settings[ 'hide_phrase' ] : SHOW_ME_THE_ADMIN_HIDE_PHRASE;

		// Does this user wish to disable the functionality?
		$user_disable = isset( $user_settings[ 'disable' ] ) && $user_settings[ 'disable' ] == true;

		?><div id="smta-user-profile-settings">
			<h2><?php _e( 'Show Me The Admin Toolbar', 'show-me-the-admin' ); ?></h2>
			<p><?php _e( 'This functionality hides your admin toolbar and enables you to make it appear, and disappear, by typing a specific phrase. You can use the phrases issued by your site administrator or you can use this setting to customize your own. <strong><em>Your "Show Toolbar when viewing site" setting must be enabled.</em></strong>', 'show-me-the-admin' ); ?></p>
			<table id="show-me-the-admin-user-profile" class="form-table show-me-the-admin-settings">
				<tbody>
					<tr>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><span><?php _e( 'Disable Show Me The Admin', 'show-me-the-admin' ); ?></span></legend>
								<label for="smta-disable"><input name="show_me_the_admin[disable]" type="checkbox" id="smta-disable" value="1"<?php checked( $user_disable ) ?>/> <strong><?php _e( 'I wish to disable this functionality', 'show-me-the-admin' ); ?></strong></label>
								<p class="description" id="tagline-description"><?php _e( "It's ok. It's not for everyone.", 'show-me-the-admin' ); ?></p>
							</fieldset>
						</td>
					</tr>
					<tr>
						<td>
							<label for="smta-show-phrase"><strong><?php _e( 'Phrase to "show" the admin bar', 'show-me-the-admin' ); ?></strong></label>
							<input name="show_me_the_admin[show_phrase]" type="text" id="smta-show-phrase" value="<?php esc_attr_e( isset( $user_settings[ 'show_phrase' ] ) ? $user_settings[ 'show_phrase' ] : null ); ?>" placeholder="<?php esc_attr_e( $default_show_phrase ); ?>" class="regular-text" />
							<p class="description" id="tagline-description"><?php printf( __( 'If left blank, will use your site\'s default phrase "%s".', 'show-me-the-admin' ), $default_show_phrase ); ?></p>
						</td>
					</tr>
					<tr>
						<td>
							<label for="smta-hide-phrase"><strong><?php _e( 'Phrase to "hide" the admin bar', 'show-me-the-admin' ); ?></strong></label>
							<input name="show_me_the_admin[hide_phrase]" type="text" id="smta-hide-phrase" value="<?php esc_attr_e( isset( $user_settings[ 'hide_phrase' ] ) ? $user_settings[ 'hide_phrase' ] : null ); ?>" placeholder="<?php esc_attr_e( $default_hide_phrase ); ?>" class="regular-text" />
							<p class="description" id="tagline-description"><?php printf( __( 'If left blank, will use your site\'s default phrase "%s".', 'show-me-the-admin' ), $default_hide_phrase ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>
		</div><?php

	}

	/**
	 * Saves custom user profile settings.
	 *
	 * check_admin_referer() is run before this action so we're good to go.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param	int - $user_id - the user ID
	 */
	public function save_user_profile_settings( $user_id ) {

		// Make sure our array is set
		if ( ! ( $show_me_the_admin = isset( $_POST[ 'show_me_the_admin' ] ) && ! empty( $_POST[ 'show_me_the_admin' ] ) ? $_POST[ 'show_me_the_admin' ] : NULL ) ) {
			return;
		}

		// Update the user meta
		update_user_meta( $user_id, 'show_me_the_admin', $show_me_the_admin );

	}

	/**
	 * Prints user admin notice for plugin functionality.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @global	$smta_users_setting_notice - boolean on whether to include users setting notice
	 * @global	$smta_user_notice - boolean on whether to include user notice
	 */
	public function print_user_admin_notice() {
		global $smta_users_setting_notice, $smta_user_notice;

		// Show the users settings notice
		if ( $smta_users_setting_notice ) {
			?><div id="smta-users-setting-notice" class="updated notice is-dismissible">
				<p><?php _e( 'Thanks for installing "Show Me The Admin". Be sure to <a href="' . $this->settings_page_url . '">explore the settings</a> to customize your phrases and set things up for your users.', 'show-me-the-admin' ); ?></p>
			</div><?php
		}

		// Show the user notice
		else if ( $smta_user_notice ) {
			?><div id="smta-user-notice" class="updated notice is-dismissible">
				<p><?php _e( 'Your site administrator has activated new functionality for your admin toolbar. Be sure to <a href="' . admin_url( 'profile.php#smta-user-profile-settings' ) . '">explore your profile settings</a> for more information.', 'show-me-the-admin' ); ?></p>
			</div><?php
		}

	}

	/**
	 * Adds a users setting notice.
	 *
	 * If no ID, then adds for the current user.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param	$user_id - the user ID
	 */
	public function add_users_setting_notice( $user_id = 0 ) {
		add_user_meta( $user_id > 0 ? $user_id : get_current_user_id(), 'show_me_the_admin_users_setting_notice', time(), true );
	}

	/**
	 * Adds a user notice.
	 *
	 * If no ID, then adds the current user.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param	$user_id - the user ID
	 */
	public function add_user_notice( $user_id = 0 ) {
		add_user_meta( $user_id > 0 ? $user_id : get_current_user_id(), 'show_me_the_admin_user_notice', time(), true );
	}

}
new Show_Me_The_Admin_Admin;