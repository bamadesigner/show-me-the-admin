<?php

class Show_Me_The_Admin_Admin {

	/**
	 * Is true when multisite
	 * and in the network admin.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @var     boolean
	 */
	public $is_network_admin;

	/**
	 * Holds the URL for the
	 * settings page.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @var     string
	 */
	public $settings_page_url;

	/**
	 * ID of the regular settings page.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @var     string
	 */
	public $settings_page_id;

	/**
	 * Holds the class instance.
	 *
	 * @since	1.0.0
	 * @access	private
	 * @var		Show_Me_The_Admin_Admin
	 */
	private static $instance;

	/**
	 * Returns the instance of this class.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return	Show_Me_The_Admin_Admin
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			$class_name = __CLASS__;
			self::$instance = new $class_name;
		}
		return self::$instance;
	}

	/**
	 * Takes care of admin shenanigans.
	 *
	 * @access  protected
	 * @since   1.0.0
	 */
	protected function __construct() {

		/*
		 * These settings let us know if we're dealing
		 * with a multisite and in the network admin.
		 */
		if ( is_multisite() && is_network_admin() ) {

			// We're in the network admin.
			$this->is_network_admin = true;

			// Define the settings page URL.
			$this->settings_page_url = add_query_arg( array( 'page' => 'show-me-the-admin' ), network_admin_url( 'settings.php' ) );

		} else {

			// We're not in the network admin.
			$this->is_network_admin = false;

			// Define the settings page URL.
			$this->settings_page_url = add_query_arg( array( 'page' => 'show-me-the-admin' ), admin_url( 'options-general.php' ) );

		}

		// Add plugin action links.
		add_filter( 'network_admin_plugin_action_links_' . SHOW_ME_THE_ADMIN_PLUGIN_FILE, array( $this, 'add_plugin_action_links' ), 10, 4 );
		add_filter( 'plugin_action_links_' . SHOW_ME_THE_ADMIN_PLUGIN_FILE, array( $this, 'add_plugin_action_links' ), 10, 4 );

		// Add multisite settings page.
		add_action( 'network_admin_menu', array( $this, 'add_network_settings_page' ) );

		// Add regular settings page.
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );

		// Add our settings meta boxes.
		add_action( 'admin_head-settings_page_show-me-the-admin', array( $this, 'add_settings_meta_boxes' ) );

		// Add styles and scripts for the settings page.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ) );

		// Update multisite settings.
		add_action( 'update_wpmu_options', array( $this, 'update_network_settings' ) );

		// Register our settings.
		add_action( 'admin_init', array( $this, 'register_settings' ), 1 );

		// Add user profile settings.
		add_action( 'profile_personal_options', array( $this, 'add_user_profile_settings' ), 0 );

		// Save user profile settings.
		add_action( 'personal_options_update', array( $this, 'save_user_profile_settings' ), 0 );
		add_action( 'edit_user_profile_update', array( $this, 'save_user_profile_settings' ), 0 );

		// Add any admin notices.
		add_action( 'admin_notices', array( $this, 'print_user_admin_notice' ) );

		// Runs an ajax call to add the users setting and user notice.
		add_action( 'wp_ajax_smta_add_users_setting_notice', array( $this, 'add_users_setting_notice' ) );
		add_action( 'wp_ajax_smta_add_user_notice', array( $this, 'smta_add_user_notice' ) );

		// Checks to see if user wants to reset network settings.
		add_action( 'admin_init', array( $this, 'user_reset_network_settings' ), 2 );

	}

	/**
	 * Method to keep our instance from
	 * being cloned or unserialized.
	 *
	 * @since	1.0.0
	 * @access	private
	 * @return	void
	 */
	private function __clone() {}
	private function __wakeup() {}

	/**
	 * Add our own plugin action links.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param   array - $actions - An array of plugin action links.
	 * @param   string - $plugin_file - Path to the plugin file.
	 * @param   array - $plugin_data - An array of plugin data.
	 * @param   string - $context - The plugin context. Defaults are 'All', 'Active',
	 *                      'Inactive', 'Recently Activated', 'Upgrade',
	 *                      'Must-Use', 'Drop-ins', 'Search'.
	 * @return  array - the filtered actions.
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

		// Make sure plugin is network activated.
		if ( ! ( function_exists( 'is_plugin_active_for_network' ) && is_plugin_active_for_network( SHOW_ME_THE_ADMIN_PLUGIN_FILE ) ) ) {
			return;
		}

		// Add the network settings page.
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
	 * @param   string - $hook_suffix - the ID of the current page.
	 * @global  $smta_users_setting_notice - boolean on whether to include users setting notice.
	 * @global  $smta_user_notice - boolean on whether to include user notice.
	 */
	public function enqueue_styles_scripts( $hook_suffix ) {
		global $smta_users_setting_notice, $smta_user_notice;

		/*
		 * We only need our styles for our
		 * settings pages and the user profile pages.
		 *
		 * Otherwise, determine whether to include
		 * our user notice scripts when needed.
		 */
		if ( in_array( $hook_suffix, array( $this->settings_page_id, 'profile.php' ) ) ) {

			// Enqueue our main styles.
			wp_enqueue_style( 'show-me-the-admin-settings', trailingslashit( plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css' ) . 'admin-settings.min.css', array(), SHOW_ME_THE_ADMIN_VERSION );

			// We only need this stuff on our settings page.
			if ( $hook_suffix == $this->settings_page_id ) {

				// Enqueue select2.
				wp_enqueue_style( 'show-me-the-admin-select2', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css' );
				wp_enqueue_script( 'show-me-the-admin-select2', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.min.js', array( 'jquery' ) );

				// Enqueue our settings script.
				wp_enqueue_script( 'show-me-the-admin-settings', trailingslashit( plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js' ) . 'show-me-the-admin-settings.min.js', array( 'jquery', 'show-me-the-admin-select2' ), SHOW_ME_THE_ADMIN_VERSION );

				// Need these scripts for the meta boxes to work correctly on our settings page.
				wp_enqueue_script( 'post' );
				wp_enqueue_script( 'postbox' );

			}
		} else {

			// Get current user ID.
			$current_user_id = get_current_user_id();

			// Don't show notices by default.
			$smta_users_setting_notice = false;
			$smta_user_notice = false;

			// Will be true if we should enqueue our user notice script.
			$enqueue_user_notice_script = false;

			// Will be true if the current user activated the plugin.
			$activated_user = get_user_meta( $current_user_id, 'show_me_the_admin_activated_user', true );

			/*
			 * Only show user settings notice for
			 * the user who activated the plugin.
			 */
			if ( false !== $activated_user && $activated_user > 0 ) {

				// Do we need to show the user settings notice?
				$users_setting_notice = get_user_meta( get_current_user_id(), 'show_me_the_admin_users_setting_notice', true );
				$show_users_setting_notice = ! ( false !== $users_setting_notice && $users_setting_notice > 0 && $users_setting_notice <= time() );

				// Include script for the users settings notice.
				if ( $show_users_setting_notice ) {

					// Make sure we include the notice.
					$smta_users_setting_notice = true;

					// Enqueue the script.
					$enqueue_user_notice_script = true;

				}
			} else {

				// Get the settings.
				$site_settings = show_me_the_admin()->get_unmodified_settings();

				// Make sure its an array.
				if ( empty( $site_settings ) ) {
					$site_settings = array();
				}

				// Disable if the user role isn't allowed.
				$user = get_userdata( $current_user_id );
				if ( isset( $site_settings['user_roles'] ) && ! ( $user->roles && is_array( $site_settings['user_roles'] ) && array_intersect( $user->roles, $site_settings['user_roles'] ) ) ) {
					$site_settings['disable'] = true;
				}

				// If this user can't have the functionality, then no point in showing the notice.
				if ( isset( $site_settings['disable'] ) && true == $site_settings['disable'] ) {
					return;
				}

				// Only print if the notice is enabled.
				if ( isset( $site_settings['enable_user_notice'] ) && true == $site_settings['enable_user_notice'] ) {

					// Do we need to include the actual user notice?
					$user_notice = get_user_meta( get_current_user_id(), 'show_me_the_admin_user_notice', true );

					// Include script for the users settings notice.
					if ( ! ( false !== $user_notice && $user_notice > 0 && $user_notice <= time() ) ) {

						// Make sure we include the notice.
						$smta_user_notice = true;

						// Enqueue the script.
						$enqueue_user_notice_script = true;

					}
				}
			}

			// Enqueue our script.
			if ( $enqueue_user_notice_script ) {
				wp_enqueue_script( 'show-me-the-admin-user-notice', trailingslashit( plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js' ) . 'show-me-the-admin-user-notice.min.js', array( 'jquery' ), SHOW_ME_THE_ADMIN_VERSION, true );
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

		// Get our settings.
		$site_settings = show_me_the_admin()->get_unmodified_settings( $this->is_network_admin );

		/*
		 * HOUSEKEEPING
		 *
		 * Used to have 'enable_login_button' as a base level setting.
		 * Make sure it gets moved to each specific feature.
		 */
		if ( isset( $site_settings['enable_login_button'] ) && true == $site_settings['enable_login_button'] ) {

			// Enable for each feature.
			if ( isset( $site_settings['features'] ) ) {
				foreach ( $site_settings['features'] as $feature ) {
					$site_settings[ "feature_{$feature}" ]['enable_login_button'] = true;
				}
			}

			// Remove the setting.
			unset( $site_settings['enable_login_button'] );

		}

		// Set the default phrases.
		$default_show_phrase = SHOW_ME_THE_ADMIN_SHOW_PHRASE;
		$default_hide_phrase = SHOW_ME_THE_ADMIN_HIDE_PHRASE;

		// If network active, get network settings for help with default phrases.
		if ( show_me_the_admin()->is_network_active ) {

			// Get network settings.
			$network_settings = show_me_the_admin()->get_unmodified_settings( true );

			// Make sure its an array.
			if ( empty( $network_settings ) ) {
				$network_settings = array();
			}

			// If no saved site settings, pull from network.
			if ( ! $site_settings ) {

				// Assign network settings.
				$site_settings = $network_settings;

			}

			// Set the default phrases.
			$default_show_phrase = ! empty( $network_settings['show_phrase'] ) ? $network_settings['show_phrase'] : $default_show_phrase;
			$default_hide_phrase = ! empty( $network_settings['hide_phrase'] ) ? $network_settings['hide_phrase'] : $default_hide_phrase;

		}

		// Make sure site settings is an array.
		if ( empty( $site_settings ) ) {
			$site_settings = array();
		}

		// About this Plugin.
		add_meta_box( 'show-me-the-admin-about-mb', __( 'About this Plugin', 'show-me-the-admin' ), array( $this, 'print_settings_meta_boxes' ), $this->settings_page_id, 'side', 'core', array( 'id' => 'about-plugin' ) );

		// Spread the Love.
		add_meta_box( 'show-me-the-admin-promote-mb', __( 'Spread the Love', 'show-me-the-admin' ), array( $this, 'print_settings_meta_boxes' ), $this->settings_page_id, 'side', 'core', array( 'id' => 'promote' ) );

		// The Features.
		add_meta_box( 'show-me-the-admin-features-mb', __( 'The Features', 'show-me-the-admin' ), array( $this, 'print_settings_meta_boxes' ), $this->settings_page_id, 'normal', 'core', array( 'id' => 'features', 'site_settings' => $site_settings ) );

		// The Users.
		add_meta_box( 'show-me-the-admin-users-mb', __( 'The Users', 'show-me-the-admin' ), array( $this, 'print_settings_meta_boxes' ), $this->settings_page_id, 'normal', 'core', array( 'id' => 'users', 'site_settings' => $site_settings ) );

		// The Settings For Feature #1.
		add_meta_box( 'show-me-the-admin-settings-keyphrase-mb', __( 'Hide toolbar and make it appear by typing a phrase', 'show-me-the-admin' ), array( $this, 'print_settings_meta_boxes' ), $this->settings_page_id, 'normal', 'core', array( 'id' => 'settings-feature-keyphrase', 'site_settings' => $site_settings, 'default_show_phrase' => $default_show_phrase, 'default_hide_phrase' => $default_hide_phrase ) );

		// The Settings For Feature #2: button.
		add_meta_box( 'show-me-the-admin-settings-button-mb', __( 'Hide toolbar and show WordPress button', 'show-me-the-admin' ), array( $this, 'print_settings_meta_boxes' ), $this->settings_page_id, 'normal', 'core', array( 'id' => 'settings-feature-button', 'site_settings' => $site_settings ) );

		// The Settings For Feature #3: Hover.
		add_meta_box( 'show-me-the-admin-settings-hover-mb', __( 'Hide toolbar and make it appear when mouse hovers near top of window', 'show-me-the-admin' ), array( $this, 'print_settings_meta_boxes' ), $this->settings_page_id, 'normal', 'core', array( 'id' => 'settings-feature-hover', 'site_settings' => $site_settings ) );

	}

	/**
	 * Print our settings meta boxes.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param   array - $post - information about the current post,
	 *      which is empty because there is no current post on a settings page.
	 * @param   array - $metabox - information about the metabox.
	 */
	public function print_settings_meta_boxes( $post, $metabox ) {

		switch ( $metabox['args']['id'] ) {

			// About meta box.
			case 'about-plugin':
				?>
				<p><?php _e( 'Hides your admin toolbar and enables you to make it appear, and disappear, using a variety of methods.', 'show-me-the-admin' ); ?></p>
				<p><strong><a href="<?php echo SHOW_ME_THE_ADMIN_PLUGIN_URL; ?>" target="_blank"><?php _e( 'Show Me The Admin', 'show-me-the-admin' ); ?></a></strong><br />
				<strong><?php _e( 'Version', 'show-me-the-admin' ); ?>:</strong> <?php echo SHOW_ME_THE_ADMIN_VERSION; ?><br /><strong><?php _e( 'Author', 'show-me-the-admin' ); ?>:</strong> <a href="http://bamadesigner.com/" target="_blank">Rachel Cherry</a></p>
				<?php
				break;

			// Promote meta box.
			case 'promote':
				?>
				<p class="star"><a href="<?php echo SHOW_ME_THE_ADMIN_PLUGIN_URL; ?>" title="<?php esc_attr_e( 'Give the plugin a good rating', 'show-me-the-admin' ); ?>" target="_blank"><span class="dashicons dashicons-star-filled"></span> <span class="promote-text"><?php _e( 'Give the plugin a good rating', 'show-me-the-admin' ); ?></span></a></p>
				<p class="twitter"><a href="https://twitter.com/bamadesigner" title="<?php _e( 'Follow bamadesigner on Twitter', 'show-me-the-admin' ); ?>" target="_blank"><span class="dashicons dashicons-twitter"></span> <span class="promote-text"><?php _e( 'Follow me on Twitter', 'show-me-the-admin' ); ?></span></a></p>
				<p class="donate"><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ZCAN2UX7QHZPL&lc=US&item_name=Rachel%20Carden%20%28Show%20Me%20The%20Admin%29&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted" title="<?php esc_attr_e( 'Donate a few bucks to the plugin', 'show-me-the-admin' ); ?>" target="_blank"><img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" alt="<?php esc_attr_e( 'Donate', 'show-me-the-admin' ); ?>" /> <span class="promote-text"><?php _e( 'and buy me a coffee', 'show-me-the-admin' ); ?></span></a></p>
				<?php
				break;

			// Features meta box.
			case 'features':

				// Get the features settings.
				$features = isset( $metabox['args']['site_settings']['features'] ) ? $metabox['args']['site_settings']['features'] : array();

				// Print the features table.
				?>
				<table id="show-me-the-admin-features" class="form-table show-me-the-admin-settings">
					<tbody>
						<tr>
							<td id="smta-features-enable-td">
								<fieldset>
									<legend><strong><?php _e( 'What features would you like to enable?', 'show-me-the-admin' ); ?></strong></legend>
									<div class="smta-choices vertical">
										<label><?php _e( '#1', 'show-me-the-admin' ); ?> - <input type="checkbox" name="show_me_the_admin[features][]" value="keyphrase"<?php checked( isset( $features ) && is_array( $features ) && in_array( 'keyphrase', $features ) ); ?> /> <?php _e( 'Hide toolbar and make it appear by typing a phrase', 'show-me-the-admin' ); ?></label>
										<label><?php _e( '#2', 'show-me-the-admin' ); ?> - <input type="checkbox" name="show_me_the_admin[features][]" value="button"<?php checked( isset( $features ) && is_array( $features ) && in_array( 'button', $features ) ); ?> /> <?php _e( 'Hide toolbar and show WordPress button in top left corner to click to appear', 'show-me-the-admin' ); ?></label>
										<label><?php _e( '#3', 'show-me-the-admin' ); ?> - <input type="checkbox" name="show_me_the_admin[features][]" value="hover"<?php checked( isset( $features ) && is_array( $features ) && in_array( 'hover', $features ) ); ?> /> <?php _e( 'Hide toolbar and make it appear when mouse hovers near top of window', 'show-me-the-admin' ); ?></label>
										<p class="description"><?php _e( 'You can customize settings for each feature in their respective section.', 'show-me-the-admin' ); ?></p>
									</div>
								</fieldset>
							</td>
						</tr>
					</tbody>
				</table>
				<?php
				break;

			// Users meta box.
			case 'users':

				// Get the user roles.
				$user_roles = get_editable_roles();

				// Print the users settings table.
				?>
				<p style="margin-bottom:0;"><?php printf( __( 'Your users have the ability to customize this functionality by %1$sediting their user profile%2$s.', 'show-me-the-admin' ), '<a href="' . admin_url( 'profile.php#smta-user-profile-settings' ) . '">', '</a>' ); ?></p>
				<table id="show-me-the-admin-user-settings" class="form-table show-me-the-admin-settings">
					<tbody>
						<tr>
							<td>
								<label for="smta-user-roles"><strong><?php _e( 'Enable for specific user roles', 'show-me-the-admin' ); ?></strong></label>
								<select id="smta-user-roles" name="show_me_the_admin[user_roles][]" multiple="multiple">
									<option value=""></option>
									<?php

									foreach ( $user_roles as $user_role_key => $user_role ) :
										?>
										<option value="<?php echo $user_role_key; ?>"<?php selected( isset( $metabox['args']['site_settings']['user_roles'] ) && is_array( $metabox['args']['site_settings']['user_roles'] ) && in_array( $user_role_key, $metabox['args']['site_settings']['user_roles'] ) ); ?>><?php echo $user_role['name']; ?></option>
										<?php
									endforeach;

									?>
								</select>
								<p class="description"><?php _e( 'If left blank, will be enabled for all users.', 'show-me-the-admin' ); ?></p>
							</td>
						</tr>
						<tr>
							<td>
								<fieldset>
									<legend class="screen-reader-text"><span><?php _e( 'Provide A User Notice', 'show-me-the-admin' ); ?></span></legend>
									<label for="smta-user-notice"><input name="show_me_the_admin[enable_user_notice]" type="checkbox" id="smta-user-notice" value="1"<?php checked( isset( $metabox['args']['site_settings']['enable_user_notice'] ) && true == $metabox['args']['site_settings']['enable_user_notice'] ); ?>/> <strong><?php _e( 'Provide an admin notice that will alert your users to this plugin\'s functionality', 'show-me-the-admin' ); ?></strong></label>
									<p class="description"><?php _e( 'Otherwise, I imagine the hidden toolbar might cause confusion.', 'show-me-the-admin' ); ?></p>
								</fieldset>
							</td>
						</tr>
					</tbody>
				</table><?php
				break;

			// Settings for keyphrase feature.
			case 'settings-feature-keyphrase':

				// Get settings.
				$feature_keyphrase = isset( $metabox['args']['site_settings']['feature_keyphrase'] ) ? $metabox['args']['site_settings']['feature_keyphrase'] : array();

				// Print the keyphrase settings.
				?>
				<table id="show-me-the-admin-settings-keyphrase" class="form-table show-me-the-admin-settings">
					<tbody>
						<tr>
							<td>
								<label for="smta-show-phrase"><strong><?php _e( 'Phrase to type to show the toolbar', 'show-me-the-admin' ); ?></strong></label>
								<input name="show_me_the_admin[show_phrase]" type="text" id="smta-show-phrase" value="<?php esc_attr( isset( $metabox['args']['site_settings']['show_phrase'] ) ? $metabox['args']['site_settings']['show_phrase'] : null ); ?>" placeholder="<?php esc_attr( $metabox['args']['default_show_phrase'] ); ?>" class="regular-text" />
								<p class="description"><?php printf( __( 'If left blank, will use the default phrase "%s".', 'show-me-the-admin' ), $metabox['args']['default_show_phrase'] ); ?></p>
							</td>
						</tr>
						<tr>
							<td>
								<label for="smta-hide-phrase"><strong><?php _e( 'Phrase to type to hide the toolbar', 'show-me-the-admin' ); ?></strong></label>
								<input name="show_me_the_admin[hide_phrase]" type="text" id="smta-hide-phrase" value="<?php esc_attr( isset( $metabox['args']['site_settings']['hide_phrase'] ) ? $metabox['args']['site_settings']['hide_phrase'] : null ); ?>" placeholder="<?php esc_attr( $metabox['args']['default_hide_phrase'] ); ?>"class="regular-text" />
								<p class="description"><?php printf( __( 'If left blank, will use the default phrase "%s".', 'show-me-the-admin' ), $metabox['args']['default_hide_phrase'] ); ?></p>
							</td>
						</tr>
						<tr>
							<td>
								<fieldset>
									<legend class="screen-reader-text"><span><?php _e( 'Reveal a WordPress Login Button', 'show-me-the-admin' ); ?></span></legend>
									<label for="smta-keyphrase-login-button"><input name="show_me_the_admin[feature_keyphrase][enable_login_button]" type="checkbox" id="smta-keyphrase-login-button" value="1"<?php checked( isset( $feature_keyphrase['enable_login_button'] ) && true == $feature_keyphrase['enable_login_button'] ); ?>/> <strong><?php _e( 'Reveal a login button if no one is logged in', 'show-me-the-admin' ); ?></strong></label>
									<p class="description"><?php _e( 'If enabled, and not logged in, typing the phrase will reveal a login button.', 'show-me-the-admin' ); ?></p>
								</fieldset>
							</td>
						</tr>
					</tbody>
				</table><?php
				break;

			// Settings for button feature.
			case 'settings-feature-button':

				// Get settings.
				$feature_button = isset( $metabox['args']['site_settings']['feature_button'] ) ? $metabox['args']['site_settings']['feature_button'] : array();

				// Print the button settings table.
				?>
				<table id="show-me-the-admin-settings-button" class="form-table show-me-the-admin-settings">
					<tbody>
						<tr>
							<td>
								<fieldset>
									<legend class="screen-reader-text"><span><?php _e( 'Show the WordPress Button', 'show-me-the-admin' ); ?></span></legend>
									<label for="smta-button-login-button"><input name="show_me_the_admin[feature_button][enable_login_button]" type="checkbox" id="smta-button-login-button" value="1"<?php checked( isset( $feature_button['enable_login_button'] ) && true == $feature_button['enable_login_button'] ); ?>/> <strong><?php _e( 'Show the WordPress button if no one is logged in', 'show-me-the-admin' ); ?></strong></label>
									<p class="description"><?php _e( 'If enabled, and not logged in, the WordPress button will reveal a login button.', 'show-me-the-admin' ); ?></p>
								</fieldset>
							</td>
						</tr>
						<tr>
							<td>
								<label class="inline" for="smta-button-mouseleave-delay"><strong><?php _e( 'Display toolbar for', 'show-me-the-admin' ); ?></strong></label>
								<input name="show_me_the_admin[feature_button][mouseleave_delay]" type="number" min="0" id="smta-button-mouseleave-delay" value="<?php echo ! empty( $feature_button['mouseleave_delay'] ) ? esc_attr( $feature_button['mouseleave_delay'] ) : '2'; ?>" placeholder="2" class="regular-text inline" /> <span><?php _e( 'second(s)', 'show-me-the-admin' ); ?></span>
								<p class="description"><?php _e( 'If enabled, define how long you want the toolbar to appear (in seconds) after you click the button.<br /><strong>The default is 2 seconds.</strong>', 'show-me-the-admin' ); ?></p>
							</td>
						</tr>
					</tbody>
				</table>
				<?php
				break;

			// Settings for hover feature.
			case 'settings-feature-hover':

				// Get settings.
				$feature_hover = isset( $metabox['args']['site_settings']['feature_hover'] ) ? $metabox['args']['site_settings']['feature_hover'] : array();

				// Print the hover settings table.
				?>
				<table id="show-me-the-admin-settings-hover" class="form-table show-me-the-admin-settings">
					<tbody>
						<tr>
							<td>
								<fieldset>
									<legend class="screen-reader-text"><span><?php _e( 'Reveal a WordPress Login Button', 'show-me-the-admin' ); ?></span></legend>
									<label for="smta-hover-login-button"><input name="show_me_the_admin[feature_hover][enable_login_button]" type="checkbox" id="smta-hover-login-button" value="1"<?php checked( isset( $feature_hover['enable_login_button'] ) && true == $feature_hover['enable_login_button'] ); ?>/> <strong><?php _e( 'Reveal a login button if no one is logged in', 'show-me-the-admin' ); ?></strong></label>
									<p class="description"><?php _e( 'If enabled, and not logged in, hovering near the top of the window will reveal a login button.', 'show-me-the-admin' ); ?></p>
								</fieldset>
							</td>
						</tr>
						<tr>
							<td>
								<label class="inline" for="smta-hover-mouseleave-delay"><strong><?php _e( 'Display toolbar for', 'show-me-the-admin' ); ?></strong></label>
								<input name="show_me_the_admin[feature_hover][mouseleave_delay]" type="number" min="0" id="smta-hover-mouseleave-delay" value="<?php echo ! empty( $feature_hover['mouseleave_delay'] ) ? esc_attr( $feature_hover['mouseleave_delay'] ) : '2'; ?>" placeholder="2" class="regular-text inline" /> <span><?php _e( 'second(s)', 'show-me-the-admin' ); ?></span>
								<p class="description"><?php _e( 'If enabled, define how long you want the toolbar to appear (in seconds) after hover.<br /><strong>The default is 2 seconds.</strong>', 'show-me-the-admin' ); ?></p>
							</td>
						</tr>
					</tbody>
				</table>
				<?php
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

		// Make sure we don't show these notices anymore since they've viewed the settings.
		$this->add_users_setting_notice();

		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php

			// Take care of settings errors.
			if ( $this->is_network_admin ) :

				// Need this to show errors in network admin.
				settings_errors( 'show_me_the_admin' );

			else :

				// If network active and viewing a site's settings, and site doesn't have its own settings, then show a message.
				if ( show_me_the_admin()->is_network_active ) :

					// Get the site settings.
					if ( ! ( $site_settings = show_me_the_admin()->get_unmodified_settings() ) ) :

						// Were the network settings reset?
						if ( isset( $_GET['network-reset'] ) ) :
							?>
							<div id="smta-users-setting-notice" class="updated notice is-dismissible">
								<p><?php _e( 'The settings have been reset to match network settings.', 'show-me-the-admin' ); ?></p>
							</div>
							<?php
						endif;

						// Print the network message if inheriting network settings.
						?>
						<div id="smta-network-settings-message" class="wp-ui-highlight">
							<span class="dashicons dashicons-info"></span>
							<p><?php _e( 'This plugin is activated network-wide and is currently inheriting the network settings. If you customize the settings below, you will overwrite the network and create custom settings for this site.', 'show-me-the-admin' ); ?></p>
							<?php

							// If the user can manage the network, give them a link.
							if ( current_user_can( 'manage_network' ) ) :
								?>
								<p><a class="button" href="<?php echo add_query_arg( array( 'page' => 'show-me-the-admin' ), network_admin_url( 'settings.php' ) ); ?>"><?php _e( 'Manage network settings', 'show-me-the-admin' ); ?></a></p>
								<?php
							endif;

							?>
						</div>
						<?php

					else :

						// Print the network message if NOT inheriting network settings.
						?>
						<div id="smta-network-settings-message" class="wp-ui-highlight">
							<span class="dashicons dashicons-info"></span>
							<p><?php _e( 'This plugin is activated network-wide but the settings below have been selected for this site only.', 'show-me-the-admin' ); ?></p>
							<p><a class="button" href="<?php echo wp_nonce_url( $this->settings_page_url, 'reset_network_settings', 'smta_nonce' ); ?>"><?php _e( 'Reset to network settings', 'show-me-the-admin' ); ?></a><?php

							// If the user can manage the network, give them a link.
							if ( current_user_can( 'manage_network' ) ) :
								?> <a class="button" href="<?php echo add_query_arg( array( 'page' => 'show-me-the-admin' ), network_admin_url( 'settings.php' ) ); ?>"><?php _e( 'Manage network settings', 'show-me-the-admin' ); ?></a><?php
							endif;

							?></p>
						</div>
						<?php

					endif;
				endif;
			endif;

			// Print the settings form.
			?>
			<form method="post" action="<?php echo ( $this->is_network_admin ) ? 'settings.php' : 'options.php'; ?>" novalidate="novalidate">
				<?php

				// Handles network and non-network settings.
				if ( $this->is_network_admin ) {
					wp_nonce_field( 'siteoptions' );
				} else {
					settings_fields( 'show_me_the_admin' );
				}

				?>
				<div id="poststuff">
					<div id="post-body" class="metabox-holder columns-2">

						<div id="postbox-container-1" class="postbox-container">

							<div id="side-sortables" class="meta-box-sortables">
								<?php do_meta_boxes( $this->settings_page_id, 'side', array() ); ?>
							</div> <!-- #side-sortables -->
							<?php

							submit_button( 'Save Changes', 'primary', 'show_me_the_admin_save_changes_side', false );

							?>
						</div> <!-- #postbox-container-1 -->

						<div id="postbox-container-2" class="postbox-container">

							<div id="normal-sortables" class="meta-box-sortables">
								<?php do_meta_boxes( $this->settings_page_id, 'normal', array() ); ?>
							</div> <!-- #normal-sortables -->

							<div id="advanced-sortables" class="meta-box-sortables">
								<?php do_meta_boxes( $this->settings_page_id, 'advanced', array() ); ?>
							</div> <!-- #advanced-sortables -->
							<?php

							submit_button( 'Save Changes', 'primary', 'show_me_the_admin_save_changes', false );

							?>
						</div> <!-- #postbox-container-2 -->
					</div> <!-- #post-body -->
					<br class="clear" />
				</div> <!-- #poststuff -->
			</form>
		</div> <!-- .wrap -->
		<?php
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
	 * Validates/updates our network setting.
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function update_network_settings() {

		// Makes sure we're saving in the network admin.
		if ( current_user_can( 'manage_network_options' )
			&& check_admin_referer( 'siteoptions' )
			&& isset( $_POST['show_me_the_admin_save_changes'] ) ) {

			// Get/update/validate the settings.
			if ( isset( $_POST['show_me_the_admin'] )
				&& ( $settings = $_POST['show_me_the_admin'] ) ) {

				// Validate the settings.
				$settings = $this->validate_settings( $settings );

				// Make sure we don't show these notices anymore since they've saved their settings.
				$this->add_users_setting_notice();

				// Update settings.
				update_site_option( 'show_me_the_admin', $settings );

				// If no errors, then show general message.
				add_settings_error( 'show_me_the_admin', 'settings_updated', __( 'Settings saved.', 'show-me-the-admin' ), 'updated' );

				// Stores any settings errors so they can be displayed on redirect.
				set_transient( 'settings_errors', get_settings_errors(), 30 );

				// Redirect to settings page.
				wp_redirect( add_query_arg( array( 'settings-updated' => 'true' ), $_REQUEST['_wp_http_referer'] ) );
				exit();

			}
		}

	}

	/**
	 * Runs when the user selects that they want
	 * to reset the network settings.
	 *
	 * @access  public
	 * @since   1.0.2
	 */
	public function user_reset_network_settings() {

		// Detect/verify our nonce.
		if (  isset( $_GET['smta_nonce'] ) && wp_verify_nonce( $_GET['smta_nonce'], 'reset_network_settings' ) ) {

			// Clear out the settings.
			update_option( 'show_me_the_admin', null );

			// Redirect to settings page.
			wp_redirect( add_query_arg( array( 'network-reset' => 'true' ), $this->settings_page_url ) );
			exit();

		}

	}

	/**
	 * Updates the 'show_me_the_admin' setting.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param   $settings - array - the settings we're sanitizing.
	 * @return  array - the updated settings.
	 */
	public function update_settings( $settings ) {

		// Validate the settings.
		$settings = $this->validate_settings( $settings );

		// Make sure we don't show these notices anymore since they've saved their settings.
		$this->add_users_setting_notice();

		// Return the validated settings.
		return $settings;

	}

	/**
	 * Validates our settings.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param   array - the settings being validated.
	 * @return  array - the validated settings.
	 */
	public function validate_settings( $settings ) {

		// Make sure text fields are sanitized.
		foreach ( array( 'show_phrase', 'hide_phrase' ) as $key ) {
			if ( isset( $settings[ $key ] ) ) {
				$settings[ $key ] = sanitize_text_field( $settings[ $key ] );
			}
		}

		// Sanitize delays.
		foreach ( array( 'button', 'hover' ) as $key ) {
			if ( isset( $settings[ "feature_{$key}" ]['mouseleave_delay'] ) ) {
				$settings[ "feature_{$key}" ]['mouseleave_delay'] = sanitize_text_field( $settings[ "feature_{$key}" ]['mouseleave_delay'] );

				// Make sure its an integer.
				if ( ! ( $settings[ "feature_{$key}" ]['mouseleave_delay'] > 0 ) ) {
					$settings[ "feature_{$key}" ]['mouseleave_delay'] = '';
				}
			}
		}

		return $settings;
	}

	/**
	 * Adds custom user profile settings.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param   WP_User - $profile_user - the current WP_User object.
	 */
	public function add_user_profile_settings( $profile_user ) {

		// Get site settings.
		$site_settings = show_me_the_admin()->get_unmodified_settings();

		// If blank and network active, merge with network settings.
		if ( ! is_array( $site_settings ) && show_me_the_admin()->is_network_active ) {

			// Make it an array.
			$site_settings = array();

			// Get network settings.
			$network_settings = show_me_the_admin()->get_unmodified_settings( true );

			// Make sure its an array.
			if ( empty( $network_settings ) ) {
				$network_settings = array();
			}

			// Remove empty values for merging.
			$site_settings = array_filter( $site_settings );
			$network_settings = array_filter( $network_settings );

			// Merge site with network settings.
			$site_settings = wp_parse_args( $site_settings, $network_settings );

		}

		// Disable if the user role isn't allowed.
		$user = get_userdata( $profile_user->ID );
		if ( isset( $site_settings['user_roles'] ) && ! ( $user->roles && is_array( $site_settings['user_roles'] ) && array_intersect( $user->roles, $site_settings['user_roles'] ) ) ) {
			$site_settings['disable'] = true;
		}

		// If this user can't have the functionality, then no point in showing the settings.
		if ( isset( $site_settings['disable'] ) && true == $site_settings['disable'] ) {
			return;
		}

		// Get the user settings.
		$user_settings = show_me_the_admin()->get_user_settings( $profile_user->ID );

		// Set the selected features.
		$user_features = isset( $user_settings['features'] ) ? $user_settings['features'] : array();

		// Set the default phrases.
		$default_show_phrase = ! empty( $site_settings['show_phrase'] ) ? $site_settings['show_phrase'] : SHOW_ME_THE_ADMIN_SHOW_PHRASE;
		$default_hide_phrase = ! empty( $site_settings['hide_phrase'] ) ? $site_settings['hide_phrase'] : SHOW_ME_THE_ADMIN_HIDE_PHRASE;

		// Make sure we don't show the notice anymore since they've viewed their profile settings.
		$this->add_user_notice();

		?>
		<div id="smta-user-profile-settings">
			<h2><?php _e( 'Show Me The Admin Toolbar', 'show-me-the-admin' ); ?></h2>
			<p><?php _e( 'The toolbar makes it really easy to move back and forth between viewing your site and editing your site but sometimes the toolbar itself can be intrusive. This functionality hides your toolbar and enables you to make it appear, and disappear, using a variety of methods. <strong><em>Your "Show Toolbar when viewing site" setting must be enabled.</em></strong>', 'show-me-the-admin' ); ?></p>
			<table id="show-me-the-admin-user-profile" class="form-table show-me-the-admin-settings smta-user-profile-settings">
				<tbody>
					<tr>
						<td>
							<fieldset>
								<legend><strong><?php _e( 'What features would you like to enable?', 'show-me-the-admin' ); ?></strong></legend>
								<div class="smta-choices vertical">
									<label><?php _e( '#1', 'show-me-the-admin' ); ?> - <input type="checkbox" name="show_me_the_admin[features][]" value="keyphrase"<?php checked( isset( $user_features ) && is_array( $user_features ) && in_array( 'keyphrase', $user_features ) ); ?> /> <?php _e( 'Hide toolbar and make it appear by typing a phrase (<em>customize the phrases below</em>)', 'show-me-the-admin' ); ?></label>
									<label><?php _e( '#2', 'show-me-the-admin' ); ?> - <input type="checkbox" name="show_me_the_admin[features][]" value="button"<?php checked( isset( $user_features ) && is_array( $user_features ) && in_array( 'button', $user_features ) ); ?> /> <?php _e( 'Hide toolbar and show WordPress button in top left corner to click to appear', 'show-me-the-admin' ); ?></label>
									<label><?php _e( '#3', 'show-me-the-admin' ); ?> - <input type="checkbox" name="show_me_the_admin[features][]" value="hover"<?php checked( isset( $user_features ) && is_array( $user_features ) && in_array( 'hover', $user_features ) ); ?> /> <?php _e( 'Hide toolbar and make it appear when mouse hovers near top of window', 'show-me-the-admin' ); ?></label>
								</div>
							</fieldset>
						</td>
					</tr>
					<tr>
						<td id="smta-features-disable-td">
							<fieldset>
								<legend class="screen-reader-text">
									<span><?php _e( 'Disable all features', 'show-me-the-admin' ); ?></span>
								</legend>
								<div class="smta-choices vertical">
									<label for="smta-features-disable">*&nbsp;&nbsp;&nbsp;<input name="show_me_the_admin[disable]" type="checkbox" id="smta-features-disable" value="1"<?php checked( isset( $user_settings['disable'] ) && true == $user_settings['disable'] ); ?>/> <strong><?php _e( 'Disable all features', 'show-me-the-admin' ); ?></strong></label>
									<p class="description"><?php _e( 'If you leave all features unchecked, it will implement the site\'s selected features. Use this setting if you would like to disable this toolbar functionality for when you are logged in.', 'show-me-the-admin' ); ?></p>
								</div>
							</fieldset>
						</td>
					</tr>
				</tbody>
			</table>

			<h3><?php _e( 'Settings For Feature #1 - Hide toolbar and make it appear by typing a phrase', 'show-me-the-admin' ); ?></h3>
			<table id="show-me-the-admin-user-profile-keyphrase" class="form-table show-me-the-admin-settings smta-user-profile-settings">
				<tbody>
					<tr>
						<td>
							<label for="smta-show-phrase"><strong><?php _e( 'Phrase to type to show the toolbar', 'show-me-the-admin' ); ?></strong></label>
							<input name="show_me_the_admin[show_phrase]" type="text" id="smta-show-phrase" value="<?php esc_attr( isset( $user_settings['show_phrase'] ) ? $user_settings['show_phrase'] : null ); ?>" placeholder="<?php esc_attr( $default_show_phrase ); ?>" class="regular-text" />
							<p class="description"><?php printf( __( 'If left blank, will use your site\'s default phrase "%s".', 'show-me-the-admin' ), $default_show_phrase ); ?></p>
						</td>
					</tr>
					<tr>
						<td>
							<label for="smta-hide-phrase"><strong><?php _e( 'Phrase to type to hide the toolbar', 'show-me-the-admin' ); ?></strong></label>
							<input name="show_me_the_admin[hide_phrase]" type="text" id="smta-hide-phrase" value="<?php esc_attr( isset( $user_settings['hide_phrase'] ) ? $user_settings['hide_phrase'] : null ); ?>" placeholder="<?php esc_attr( $default_hide_phrase ); ?>" class="regular-text" />
							<p class="description"><?php printf( __( 'If left blank, will use your site\'s default phrase "%s".', 'show-me-the-admin' ), $default_hide_phrase ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Saves custom user profile settings.
	 *
	 * check_admin_referer() is run before this action so we're good to go.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param   int - $user_id - the user ID.
	 */
	public function save_user_profile_settings( $user_id ) {

		// Make sure our array is set.
		if ( ! ( $show_me_the_admin = ! empty( $_POST['show_me_the_admin'] ) ? $_POST['show_me_the_admin'] : null ) ) {
			return;
		}

		// Update the user meta.
		update_user_meta( $user_id, 'show_me_the_admin', $show_me_the_admin );

		// Make sure we don't show the notice anymore since they've saved their profile settings.
		$this->add_user_notice();

	}

	/**
	 * Prints user admin notice for plugin functionality.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @global  $smta_users_setting_notice - boolean on whether to include users setting notice.
	 * @global  $smta_user_notice - boolean on whether to include user notice.
	 */
	public function print_user_admin_notice() {
		global $smta_users_setting_notice, $smta_user_notice;

		// Show the users settings and user notice.
		if ( $smta_users_setting_notice ) :

			?>
			<div id="smta-users-setting-notice" class="updated notice is-dismissible">
				<p><?php printf( __( 'Thank you for installing "Show Me The Admin". Be sure to %1$sexplore the settings%2$s to customize its functionality for you and your users.', 'show-me-the-admin' ), '<a href="' . $this->settings_page_url . '">', '</a>' ); ?></p>
			</div>
			<?php
		elseif ( $smta_user_notice ) :

			?>
			<div id="smta-user-notice" class="updated notice is-dismissible">
				<p><?php printf( __( 'Your site administrator has activated new functionality for your admin toolbar. Be sure to %1$sexplore your profile settings%2$s for more information.', 'show-me-the-admin' ), '<a href="' . admin_url( 'profile.php#smta-user-profile-settings' ) . '">', '</a>' ); ?></p>
			</div>
			<?php
		endif;
	}

	/**
	 * Adds a users setting notice timestamp.
	 *
	 * If set, will not display the plugin settings
	 * notice to the user who activated the plugin.
	 *
	 * If no ID, then adds for the current user.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param   $user_id - int - the user ID.
	 */
	public function add_users_setting_notice( $user_id = 0 ) {
		add_user_meta( $user_id > 0 ? $user_id : get_current_user_id(), 'show_me_the_admin_users_setting_notice', time(), true );
	}

	/**
	 * Adds a user notice timestamp.
	 *
	 * If set, will not display the profile
	 * settings notice to the site's users.
	 *
	 * If no ID, then adds the current user.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param   $user_id - int - the user ID.
	 */
	public function add_user_notice( $user_id = 0 ) {
		add_user_meta( $user_id > 0 ? $user_id : get_current_user_id(), 'show_me_the_admin_user_notice', time(), true );
	}
}

/**
 * Returns the instance of our main Show_Me_The_Admin_Admin class.
 *
 * Will come in handy when we need to access the
 * class to retrieve data throughout the plugin.
 *
 * @since	1.0.0
 * @access	public
 * @return	Show_Me_The_Admin_Admin
 */
function show_me_the_admin_admin() {
	return Show_Me_The_Admin_Admin::instance();
}

// Let's get this show on the road.
show_me_the_admin_admin();
