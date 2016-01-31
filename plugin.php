<?php

/**
 * Plugin Name:       Show Me The Admin
 * Plugin URI:        https://github.com/bamadesigner/show-me-the-admin
 * Description:       Hides your admin toolbar and enables you to make it appear, and disappear, by typing a specific phrase.
 * Version:           1.0.0
 * Author:            Rachel Carden
 * Author URI:        https://bamadesigner.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       show-me-the-admin
 * Domain Path:       /languages
 */

// @TODO will need a way to let users know about functionality and allow them to enable/disable/setup their phrases
// @TODO make sure we delete settings when plugin is deleted

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// If you define them, will they be used?
define( 'SHOW_ME_THE_ADMIN_VERSION', '1.0.0' );
define( 'SHOW_ME_THE_ADMIN_PLUGIN_URL', 'https://wordpress.org/plugins/show-me-the-admin/' );
define( 'SHOW_ME_THE_ADMIN_PLUGIN_FILE', 'show-me-the-admin/plugin.php' );
define( 'SHOW_ME_THE_ADMIN_SHOW_PHRASE', 'showme' );
define( 'SHOW_ME_THE_ADMIN_HIDE_PHRASE', 'hideme' );

// We only need you in the admin
if ( is_admin() ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/admin.php';
}

class Show_Me_The_Admin {

	/**
	 * Whether or not this plugin is network active.
	 *
	 * @since	1.0.0
	 * @access	public
	 * @var		boolean
	 */
	public $is_network_active;

	/**
	 * Will hold whether or not the
	 * user wants the admin bar.
	 *
	 * @since	1.0.0
	 * @access	public
	 * @var		boolean
	 */
	public $user_wants_admin_bar;

	/**
	 * Will hold the user's settings.
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
		if ( ! isset( static::$instance ) ) {
			$className = __CLASS__;
			static::$instance = new $className;
		}
		return static::$instance;
	}

	/**
	 * Warming things up.
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	protected function __construct() {

		// Is this plugin network active?
		$this->is_network_active = is_multisite() && ( $plugins = get_site_option( 'active_sitewide_plugins' ) ) && isset( $plugins[ SHOW_ME_THE_ADMIN_PLUGIN_FILE ] );

		// Load our textdomain
		add_action( 'init', array( $this, 'textdomain' ) );

		// Runs on install
		register_activation_hook( __FILE__, array( $this, 'install' ) );

		// Runs when the plugin is upgraded
		add_action( 'upgrader_process_complete', array( $this, 'upgrader_process_complete' ), 1, 2 );

		// Detects the user's admin bar preference
		add_action( 'plugins_loaded', array( $this, 'get_admin_bar_pref' ), 1 );

		// Add needed styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ) );

		// Print dropdown login button
		add_action( 'wp_footer', array( $this, 'print_login_button' ), 2000 );

	}

	/**
	 * Method to keep our instance from being cloned.
	 *
	 * @since	1.0.0
	 * @access	private
	 * @return	void
	 */
	private function __clone() {}

	/**
	 * Method to keep our instance from being unserialized.
	 *
	 * @since	1.0.0
	 * @access	private
	 * @return	void
	 */
	private function __wakeup() {}

	/**
	 * Runs when the plugin is installed.
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function install() {}

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
	 * @TODO Add language files
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function textdomain() {
		load_plugin_textdomain( 'show-me-the-admin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Returns a user's settings. If no user ID
	 * is passed, gets settings for current user.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param	int - $user_id - the user ID
	 * @return  array - the settings
	 */
	public function get_user_settings( $user_id = 0 ) {

		// Make sure we have a valid user iD
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		// Get the user settings
		$user_settings = $user_id > 0 ? get_user_meta( $user_id, 'show_me_the_admin', true ) : array();

		// Make sure its an array
		if ( empty( $user_settings ) ) {
			$user_settings = array();
		}

		return $user_settings;
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

		// If already set, return the settings
		if ( isset( static::$settings ) ) {
			return static::$settings;
		}

		// Get site settings
		$site_settings = get_option( 'show_me_the_admin', array() );

		// Make sure its an array
		if ( empty( $site_settings ) ) {
			$site_settings = array();
		}

		// If network active, merge with network settings
		if ( $this->is_network_active ) {

			// Get network settings
			$network_settings = get_site_option( 'show_me_the_admin', array() );

			// Make sure its an array
			if ( empty( $network_settings ) ) {
				$network_settings = array();
			}

			// Remove empty values for merging
			$site_settings = array_filter( $site_settings );
			$network_settings = array_filter( $network_settings );

			// Merge site with network settings
			$site_settings = wp_parse_args( $site_settings, $network_settings );

		}

		// If logged in, merge with user settings
		if ( function_exists( 'is_user_logged_in' ) && is_user_logged_in() ) {

			// Get user settings
			$user_settings = $this->get_user_settings();

			// Remove empty values for merging
			$site_settings = array_filter( $site_settings );
			$user_settings = array_filter( $user_settings );

			// Merge site with user settings
			$site_settings = wp_parse_args( $user_settings, $site_settings );

		}

		// Store the settings
		return static::$settings = apply_filters( 'show_me_the_admin_settings', $site_settings );
	}

	/**
	 * Return the keycode for a specific phrase.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param	string - the phrase
	 * @return  string|null - the keycode or null if it doesn't exist
	 */
	public function get_phrase_keycode( $phrase ) {

		// Make sure the phrase only has alphanumeric characters
		$phrase = preg_replace( '/[^a-z0-9]/i', '', $phrase );

		// Split phrase into array
		$phrase = str_split( $phrase );

		// Make sure we have a phrase
		if ( empty( $phrase ) ) {
			return null;
		}

		// Will hold the entire keycode
		$keycode = '';

		// Build the phrase
		foreach( $phrase as $key ) {
			if ( $code = $this->get_keycode( $key ) ) {
				$keycode .= $code;
			}
		}

		// Return keycode
		return ! empty( $keycode ) ? $keycode : null;

	}

	/**
	 * Return the code for a specific key.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param	string - the key
	 * @return  string|null - the code or null if it doesn't exist
	 */
	public function get_keycode( $key ) {

		// Index the keycodes by key
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

		// Return the code
		return isset( $keycodes[$key] ) && ! empty( $keycodes[$key] ) ? $keycodes[$key] : null;
	}

	/**
	 * Detects the user's admin bar preference.
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_admin_bar_pref() {
		$this->user_wants_admin_bar = _get_admin_bar_pref();
	}

	/**
	 * Add styles and scripts for our shortcodes.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param	string - $hook_suffix - the ID of the current page
	 * @filter	show_me_the_admin_show_phrase
	 * @filter	show_me_the_admin_hide_phrase
	 */
	public function enqueue_styles_scripts() {

		// Get the settings
		$settings = $this->get_settings();

		// If logged in...
		if ( is_user_logged_in() ) {

			// Don't add if the user doesn't want the admin bar
			if ( ! $this->user_wants_admin_bar ) {
				return;
			}

			// Don't add if the user doesn't want the functionality
			if ( isset( $settings[ 'disable' ] ) && $settings[ 'disable' ] == true ) {
				return;
			}

		}

		// If not logged in...
		else {

			// Don't add if the login button is not enabled
			if ( ! ( isset( $settings[ 'enable_login_button' ] ) && $settings[ 'enable_login_button' ] == true ) ) {
				return;
			}

		}

		// Build our data array
		$localize = array();

		// Add 'show_phrase'
		$show_phrase = isset( $settings[ 'show_phrase' ] ) ? $this->get_phrase_keycode( $settings[ 'show_phrase' ] ) : $this->get_phrase_keycode( SHOW_ME_THE_ADMIN_SHOW_PHRASE );
		$localize[ 'show_phrase' ] = apply_filters( 'show_me_the_admin_show_phrase', $show_phrase );

		// Add 'hide_phrase'
		$hide_phrase = isset( $settings[ 'hide_phrase' ] ) ? $this->get_phrase_keycode( $settings[ 'hide_phrase' ] ) : $this->get_phrase_keycode( SHOW_ME_THE_ADMIN_HIDE_PHRASE );
		$localize[ 'hide_phrase' ] = apply_filters( 'show_me_the_admin_hide_phrase', $hide_phrase );

		// Enqueue the script
		wp_enqueue_script( 'show-me-the-admin', trailingslashit( plugin_dir_url( __FILE__ ) . 'js' ) . 'show-me-the-admin.min.js', array( 'jquery' ), SHOW_ME_THE_ADMIN_VERSION, true );

		// Pass some data
		wp_localize_script( 'show-me-the-admin', 'show_me_the_admin', $localize );

		// Hide the bar out the gate
		?><style type="text/css" media="screen">
			#wpadminbar, #wpadminbar.hidden { display:none; }
			html.hide-show-me-the-admin-bar, * html.hide-show-me-the-admin-bar body { margin-top: 0 !important; }
			#show-me-the-admin-login{
				background: #23282d;
				width: 100%;
				height: 32px;
				color: #fff;
				font-weight: 400;
				font-size: 15px;
				line-height: 32px;
				position: fixed;
				top: 0;
				left: 0;
				z-index: 99999;
				text-align: center;
				text-transform: uppercase;
				text-decoration: none;
			}
			#show-me-the-admin-login:hover{background:#21759b;}
		</style>
		<script type="text/javascript">
			document.documentElement.className = 'hide-show-me-the-admin-bar';
		</script><?php

	}

	/**
	 * Print the dropdown login button.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @filter	show_me_the_admin_login_text
	 */
	public function print_login_button() {

		// Get the settings
		$settings = $this->get_settings();

		// Don't print if the user is logged in or the admin bar is showing
		if ( is_user_logged_in() || is_admin_bar_showing() ) {
			return;
		}

		// Don't print if not logged in and the login button is not enabled
		if ( ! ( isset( $settings[ 'enable_login_button' ] ) && $settings[ 'enable_login_button' ] == true ) ) {
			return;
		}

		// Print the login button with redirect
		$login_redirect = isset( $_SERVER[ 'REQUEST_URI' ] ) ? $_SERVER[ 'REQUEST_URI' ] : null;

		// Set the button label
		$login_label = apply_filters( 'show_me_the_admin_login_text', __( 'Login to WordPress', 'show-me-the-admin' ) );

		// Print the button
		?><a id="show-me-the-admin-login" href="<?php echo wp_login_url( site_url( $login_redirect ) ); ?>"><?php echo $login_label; ?></a><?php

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

// Let's get this show on the road
show_me_the_admin();