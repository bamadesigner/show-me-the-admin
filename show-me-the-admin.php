<?php
/**
 * Plugin Name:       Show Me The Admin
 * Plugin URI:        https://wordpress.org/plugins/show-me-the-admin/
 * Description:       Hides your admin toolbar and enables you to make it appear, and disappear, using a variety of methods.
 * Version:           1.2.1
 * Author:            Rachel Cherry
 * Author URI:        https://bamadesigner.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       show-me-the-admin
 * Domain Path:       /languages
 */

/*
 * @TODO:
 * - Add language files
 * - Replace/remove select2 (not accessible)
 * - Add a link to or embed a demo video to help users understand functionality
 * - Test ability to do sound recognition - https://github.com/daveross/speak-to-wp/blob/master/assets/js/speak-to-wp.js
 * - Allow custom key phrases to link directly to WP Admin pages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// We only need you in the admin.
if ( is_admin() ) {
	require_once plugin_dir_path( __FILE__ ) . 'inc/admin.php';
}

class Show_Me_The_Admin {

	/**
	 * Plugin information we need.
	 *
	 * @since   1.2.0
	 * @access  public
	 * @var     string
	 */
	public $version = '1.2.1',
		$plugin_url = 'https://wordpress.org/plugins/show-me-the-admin/',
		$plugin_file = 'show-me-the-admin/show-me-the-admin.php',
		$show_phrase = 'showme',
		$hide_phrase = 'hideme';

	/**
	 * Whether or not this plugin is network active.
	 *
	 * @since	1.0.0
	 * @access	public
	 * @var		boolean
	 */
	public $is_network_active;

	/**
	 * Will hold whether or not
	 * hiding the admin should be enabled
	 * for particular features.
	 *
	 * @since	1.0.1
	 * @access	private
	 * @var		array
	 */
	private static $enable_hide_the_admin_bar;

	/**
	 * Will hold the plugin's unmodified settings.
	 *
	 * @since	1.0.0
	 * @access	private
	 * @var		array
	 */
	private static $unmodified_settings;

	/**
	 * Will hold the user's settings.
	 *
	 * @since	1.0.0
	 * @access	private
	 * @var		array
	 */
	private static $user_settings;

	/**
	 * Will hold the plugin's settings.
	 *
	 * @since	1.0.0
	 * @access	private
	 * @var		array
	 */
	private static $settings;

	/**
	 * Holds the class instance.
	 *
	 * @since	1.0.0
	 * @access	private
	 * @var		Show_Me_The_Admin
	 */
	private static $instance;

	/**
	 * Returns the instance of this class.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return	Show_Me_The_Admin
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			$class_name = __CLASS__;
			self::$instance = new $class_name;
		}
		return self::$instance;
	}

	/**
	 * Warming things up.
	 *
	 * @access  protected
	 * @since   1.0.0
	 */
	protected function __construct() {

		// Is this plugin network active?
		$this->is_network_active = is_multisite() && ( $plugins = get_site_option( 'active_sitewide_plugins' ) ) && isset( $plugins[ $this->plugin_file ] );

		// Load our textdomain.
		add_action( 'init', array( $this, 'textdomain' ) );

		// Runs on install.
		register_activation_hook( __FILE__, array( $this, 'install' ) );

		// Runs when the plugin is upgraded.
		add_action( 'upgrader_process_complete', array( $this, 'upgrader_process_complete' ), 1, 2 );

		// Add needed styles and scripts.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ) );

		// Filters the body class.
		add_filter( 'body_class', array( $this, 'filter_body_class' ), 100000, 2 );

		// Print dropdown login button.
		add_action( 'wp_footer', array( $this, 'print_login_button' ), 2000 );

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
	 * Runs when the plugin is installed.
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function install() {

		// Add this option so we know who enabled the plugin.
		add_user_meta( get_current_user_id(), 'show_me_the_admin_activated_user', time(), true );

	}

	/**
	 * Runs when the plugin is upgraded.
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function upgrader_process_complete() {}

	/**
	 * Internationalization FTW.
	 * Load our textdomain.
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function textdomain() {
		load_plugin_textdomain( 'show-me-the-admin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Returns the plugin's default settings.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  array - the settings
	 */
	public function get_default_settings() {
		return array(
			'features'                  => array(
				'keyphrase',
				'button',
			),
			'feature_keyphrase'         => array(
				'enable_login_button'   => true,
			),
			'feature_button'            => array(
				'mouseleave_delay'      => 2,
			),
			'feature_hover'             => array(
				'mouseleave_delay'      => 2,
			),
			'user_roles'                => array( 'administrator' ),
			'enable_user_notice'        => true,
		);
	}

	/**
	 * Returns our straight-forward, unmodified settings.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param   boolean - $network - whether or not to retrieve network settings.
	 * @return  array - the settings
	 */
	public function get_unmodified_settings( $network = false ) {

		// If already set, return the settings.
		if ( $network ) {
			if ( isset( self::$unmodified_settings['network'] ) ) {
				return self::$unmodified_settings['network'];
			}
		} elseif ( isset( self::$unmodified_settings['site'] ) ) {
			return self::$unmodified_settings['site'];
		}

		// Get default settings.
		$default_settings = $this->get_default_settings();

		/*
		 * If network active, then we have to treat the settings carefully.
		 *
		 * Otherwise, then just get site settings and mix with default.
		 */
		if ( $this->is_network_active ) {

			/*
			 * If after network settings, then just get network settings.
			 *
			 * Otherwise, if they're after site settings then make sure
			 * they don't pick up default settings that could overwrite network.
			 */
			if ( $network ) {
				$unmodified_settings = get_site_option( 'show_me_the_admin', $default_settings );
			} else {
				$unmodified_settings = get_option( 'show_me_the_admin' );
			}
		} else {
			$unmodified_settings = get_option( 'show_me_the_admin', $default_settings );
		}

		// Store the settings.
		return self::$unmodified_settings[ ( $network ? 'network' : 'site' ) ] = $unmodified_settings;
	}

	/**
	 * Returns a user's unmodified settings.
	 *
	 * If no user ID is passed, gets settings for current user.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param   $user_id - int - the user ID.
	 * @return  array - the settings
	 */
	public function get_user_settings( $user_id = 0 ) {

		// If already set, return the settings.
		if ( isset( self::$user_settings ) ) {
			return self::$user_settings;
		}

		// Make sure we have a valid user ID.
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		// Get the user meta.
		return self::$user_settings = $user_id > 0 ? get_user_meta( $user_id, 'show_me_the_admin', true ) : false;
	}

	/**
	 * Returns settings for the front-end.
	 * Allows for adjusting settings as needed.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @filter	show_me_the_admin_settings
	 * @return  array - the settings
	 */
	public function get_settings() {

		// If already set, return the settings.
		if ( isset( self::$settings ) ) {
			return self::$settings;
		}

		// Get site settings.
		$site_settings = $this->get_unmodified_settings();

		// If blank and network active, merge with network settings.
		if ( ! is_array( $site_settings ) && $this->is_network_active ) {

			// Make it an array.
			$site_settings = array();

			// Get network settings.
			$network_settings = $this->get_unmodified_settings( true );

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

		// If logged in, merge with user settings.
		if ( function_exists( 'is_user_logged_in' ) && is_user_logged_in() ) {

			// Get user settings.
			$current_user_id = get_current_user_id();
			$user_settings = $this->get_user_settings( $current_user_id );

			// If an array, it means the user has set them so pay attention.
			if ( is_array( $user_settings ) ) {

				// The only setting we need concern ourselves with for merging is the features setting.
				if ( ! empty( $user_settings['features'] ) ) {

					// Assign site features setting with user features setting.
					$site_settings['features'] = $user_settings['features'];

				}

				// Merge the rest.
				$site_settings = wp_parse_args( $user_settings, $site_settings );

			}

			// Disable if the user role isn't allowed.
			$user = get_userdata( $current_user_id );
			if ( isset( $site_settings['user_roles'] ) && ! ( $user->roles && is_array( $site_settings['user_roles'] ) && array_intersect( $user->roles, $site_settings['user_roles'] ) ) ) {
				$site_settings['disable'] = true;
			}
		}

		// Store the settings.
		return self::$settings = apply_filters( 'show_me_the_admin_settings', $site_settings );
	}

	/**
	 * Return the keycode for a specific phrase.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param   $phrase - string - the phrase.
	 * @return  string|null - the keycode or null if it doesn't exist.
	 */
	public function get_phrase_keycode( $phrase ) {

		// Make sure the phrase only has alphanumeric characters.
		$phrase = preg_replace( '/[^a-z0-9]/i', '', $phrase );

		// Split phrase into array.
		$phrase = str_split( $phrase );

		// Make sure we have a phrase.
		if ( empty( $phrase ) ) {
			return null;
		}

		// Will hold the entire keycode.
		$keycode = '';

		// Build the phrase.
		foreach ( $phrase as $key ) {
			if ( $code = $this->get_keycode( $key ) ) {
				$keycode .= $code;
			}
		}

		// Return keycode.
		return ! empty( $keycode ) ? $keycode : null;
	}

	/**
	 * Return the code for a specific key.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param   $key - string - the key.
	 * @return  string|null - the code or null if it doesn't exist.
	 */
	public function get_keycode( $key ) {

		// Index the keycodes by key.
		$keycodes = array(
			'0' => '48',
			'1' => '49',
			'2' => '50',
			'3' => '51',
			'4' => '52',
			'5' => '53',
			'6' => '54',
			'7' => '55',
			'8' => '56',
			'9' => '57',
			'a' => '65',
			'b' => '66',
			'c' => '67',
			'd' => '68',
			'e' => '69',
			'f' => '70',
			'g' => '71',
			'h' => '72',
			'i' => '73',
			'j' => '74',
			'k' => '75',
			'l' => '76',
			'm' => '77',
			'n' => '78',
			'o' => '79',
			'p' => '80',
			'q' => '81',
			'r' => '82',
			's' => '83',
			't' => '84',
			'u' => '85',
			'v' => '86',
			'w' => '87',
			'x' => '88',
			'y' => '89',
			'z' => '90',
		);

		// Return the code.
		return ! empty( $keycodes[ $key ] ) ? $keycodes[ $key ] : null;
	}

	/**
	 * Returns true if we should hide the toolbar.
	 * You can test against a specific feature.
	 *
	 * @access  public
	 * @since   1.0.1
	 * @param   $feature - string - the feature we're checking (keyphrase, button, hover).
	 * @return  bool - true if we should hide the toolbar.
	 */
	public function enable_hide_the_admin_bar( $feature = '' ) {

		// If it's already been tested, will be false or an array.
		if ( false === self::$enable_hide_the_admin_bar ) {
			return false;
		} elseif ( is_array( self::$enable_hide_the_admin_bar ) ) {

			/*
			 * If specific feature, see if it has already been decided.
			 *
			 * Otherwise, if not empty then means something is enabled.
			 */
			if ( ! empty( $feature ) ) {
				return in_array( $feature, self::$enable_hide_the_admin_bar );
			}

			return ! empty( self::$enable_hide_the_admin_bar );
		}

		// Don't add if the user doesn't want the toolbar.
		$is_user_logged_in = is_user_logged_in();
		if ( $is_user_logged_in && ! _get_admin_bar_pref() ) {
			self::$enable_hide_the_admin_bar = false;
			return false;
		}

		$settings = $this->get_settings();

		// Don't add if functionality is disabled for this user.
		if ( $is_user_logged_in && isset( $settings['disable'] ) && true == $settings['disable'] ) {
			self::$enable_hide_the_admin_bar = false;
			return false;
		}

		// Check to make sure any features are set.
		if ( empty( $settings['features'] ) ) {
			self::$enable_hide_the_admin_bar = false;
			return false;
		}

		// Make sure it's an array.
		if ( ! is_array( $settings['features'] ) ) {
			$settings['features'] = explode( ',', str_replace( ' ', '', $settings['features'] ) );
		}

		// Create array for storage.
		$enable_hide_the_admin_bar = $settings['features'];

		// If not logged in, see if we want the login button.
		if ( ! $is_user_logged_in ) {
			foreach ( $enable_hide_the_admin_bar as $this_feature ) {

				// Remove if the login button is not enabled for feature.
				if ( ! ( isset( $settings[ "feature_{$this_feature}" ]['enable_login_button'] ) && true == $settings[ "feature_{$this_feature}" ]['enable_login_button'] ) ) {
					unset( $enable_hide_the_admin_bar[ array_search( $this_feature, $enable_hide_the_admin_bar ) ] );
				}
			}
		}

		// Store the settings.
		self::$enable_hide_the_admin_bar = $enable_hide_the_admin_bar;

		// First round test.
		if ( ! empty( $feature ) ) {
			return in_array( $feature, $enable_hide_the_admin_bar );
		}
		return ! empty( $enable_hide_the_admin_bar );
	}

	/**
	 * Add styles and scripts for our shortcodes.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @filter  show_me_the_admin_show_phrase
	 * @filter  show_me_the_admin_hide_phrase
	 */
	public function enqueue_styles_scripts() {

		// If we shouldn't hide the toolbar, then get out of here.
		if ( ! $this->enable_hide_the_admin_bar() ) {
			return;
		}

		// Get the settings.
		$settings = $this->get_settings();

		// Build our data array.
		$localize = array( 'features' => array() );

		// Set style dependencies.
		$style_dep = array();

		// If keyphrase is enabled, add settings.
		if ( $this->enable_hide_the_admin_bar( 'keyphrase' ) ) {

			$localize['features'][] = 'keyphrase';

			// Add 'show_phrase'.
			$show_phrase = ! empty( $settings['show_phrase'] ) ? $this->get_phrase_keycode( $settings['show_phrase'] ) : $this->get_phrase_keycode( $this->show_phrase );
			$localize['show_phrase'] = apply_filters( 'show_me_the_admin_show_phrase', $show_phrase );

			// Add 'hide_phrase'.
			$hide_phrase = ! empty( $settings['hide_phrase'] ) ? $this->get_phrase_keycode( $settings['hide_phrase'] ) : $this->get_phrase_keycode( $this->hide_phrase );
			$localize['hide_phrase'] = apply_filters( 'show_me_the_admin_hide_phrase', $hide_phrase );

		}

		// If button is enabled, add settings.
		if ( $this->enable_hide_the_admin_bar( 'button' ) ) {

			$localize['features'][] = 'button';

			// We need the font for the WordPress logo.
			$style_dep[] = 'dashicons';

			// Define the mouseleave delay, default is 2 seconds.
			$mouseleave_delay = 2;
			if ( ! empty( $settings['feature_button']['mouseleave_delay'] ) ) {
				if ( $settings['feature_button']['mouseleave_delay'] > 0 ) {
					$mouseleave_delay = $settings['feature_button']['mouseleave_delay'];
				}
			}

			// Convert to milliseconds.
			$mouseleave_delay = $mouseleave_delay * 1000;

			// Send to script.
			$localize['button_mouseleave_delay'] = $mouseleave_delay;

		}

		// If hover is enabled, add settings.
		if ( $this->enable_hide_the_admin_bar( 'hover' ) ) {

			$localize['features'][] = 'hover';

			// Define the mouseleave delay, default is 2 seconds.
			$mouseleave_delay = 2;
			if ( ! empty( $settings['feature_hover']['mouseleave_delay'] ) && $settings['feature_hover']['mouseleave_delay'] > 0 ) {
				$mouseleave_delay = $settings['feature_hover']['mouseleave_delay'];
			}

			// Convert to milliseconds.
			$mouseleave_delay = $mouseleave_delay * 1000;

			// Send to script.
			$localize['hover_mouseleave_delay'] = $mouseleave_delay;

		}

		// No point if localize is empty.
		if ( empty( $localize ) || empty( $localize['features'] ) ) {
			return;
		}

		// Enqueue the style.
		wp_enqueue_style( 'show-me-the-admin', trailingslashit( plugin_dir_url( __FILE__ ) . 'assets/css' ) . 'show-me-the-admin.min.css', $style_dep, $this->version );

		// Enqueue the script.
		wp_enqueue_script( 'show-me-the-admin', trailingslashit( plugin_dir_url( __FILE__ ) . 'assets/js' ) . 'show-me-the-admin.min.js', array( 'jquery' ), $this->version, true );

		// Pass some data.
		wp_localize_script( 'show-me-the-admin', 'show_me_the_admin', $localize );

		// Hide the bar out the gate.
		?>
		<style type="text/css" media="screen">
			#wpadminbar, #wpadminbar.hidden { display:none; }
			html.hide-show-me-the-admin-bar, * html.hide-show-me-the-admin-bar body { margin-top: 0 !important; }
		</style>
		<script type="text/javascript">
			document.documentElement.className = 'hide-show-me-the-admin-bar';
		</script>
		<?php
	}

	/**
	 * Filters the body class for classes we don't need.
	 *
	 * @access  public
	 * @since   1.0.1
	 * @param   $classes - array - An array of body classes.
	 * @param   $class - array - An array of additional classes added to the body.
	 * @return  array - the filtered array of body classes.
	 */
	public function filter_body_class( $classes, $class ) {

		// If we shouldn't hide the toolbar, then get out of here.
		if ( ! $this->enable_hide_the_admin_bar() ) {
			return $classes;
		}

		// Remove any theme's admin-bar CSS so it gets rid of their styles.
		unset( $classes[ array_search( 'admin-bar', $classes ) ] );

		return $classes;
	}

	/**
	 * Print the dropdown login button.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @filter	show_me_the_admin_login_text
	 */
	public function print_login_button() {

		// If the button feature is enabled, we need to add the button.
		if ( $this->enable_hide_the_admin_bar( 'button' ) ) :
			?><button id="show-me-the-admin-button" tabindex="1" title="<?php printf( esc_attr__( 'Show the %s admin bar', 'show-me-the-admin' ), 'WordPress' ); ?>"></button><?php
		endif;

		// If the hover feature is enabled, we need an element to tie it to.
		if ( $this->enable_hide_the_admin_bar( 'hover' ) ) :
			?><div id="show-me-the-admin-hover"></div><?php
		endif;

		// If not logged in...
		if ( ! is_user_logged_in() ) :

			// Show the login button if a feature is enabled.
			if ( ! is_admin_bar_showing() && $this->enable_hide_the_admin_bar() ) :

				// Print the login button with redirect.
				$login_redirect = ! empty( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : null;

				// Set the button label.
				$login_label = apply_filters( 'show_me_the_admin_login_text', sprintf( __( 'Login to %s', 'show-me-the-admin' ), 'WordPress' ) );

				?><a id="show-me-the-admin-login" href="<?php echo wp_login_url( site_url( $login_redirect ) ); ?>"><?php echo $login_label; ?></a><?php

			endif;
		endif;
	}
}

/**
 * Returns the instance of our main Show_Me_The_Admin class.
 *
 * Will come in handy when we need to access the
 * class to retrieve data throughout the plugin.
 *
 * @since	1.0.0
 * @access	public
 * @return	Show_Me_The_Admin
 */
function show_me_the_admin() {
	return Show_Me_The_Admin::instance();
}

// Let's get this show on the road.
show_me_the_admin();
