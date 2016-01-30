<?php

/**
 * Plugin Name:       Show Me The Admin
 * Plugin URI:        https://github.com/bamadesigner/show-me-the-admin
 * Description:       Allows you to quickly hide and show your admin bar by typing a specific phrase.
 * Version:           1.0.0
 * Author:            Rachel Carden
 * Author URI:        https://bamadesigner.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       show-me-the-admin
 * Domain Path:       /languages
 */

// @TODO add filters
// @TODO make sure all text is I18n
// @TODO setup user profile settings so users can set their own phrases
// @TODO add setting so you can say you don't want the functionality when not logged in
// @TODO will need a way to let users know about functionality and allow them to enable/disable/setup their phrases

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// If you define them, will they be used?
define( 'SHOW_ME_THE_ADMIN_VERSION', '1.0.0' );
define( 'SHOW_ME_THE_ADMIN_PLUGIN_URL', 'https://wordpress.org/plugins/show-me-the-admin/' );
define( 'SHOW_ME_THE_ADMIN_PLUGIN_FILE', 'show-me-the-admin/plugin.php' );

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
	 * Will hold whether or not to add admin bar functionality.
	 *
	 * @since	1.0.0
	 * @access	public
	 * @var		boolean
	 */
	public $display_admin_bar;

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

		// Add needed styles to the <head>
		add_action( 'wp_head', array( $this, 'add_styles_scripts_to_head' ) );

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
	 * A function to gather settings
	 * for the front-end. Allows for adjusting
	 * settings as needed.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  array|false - array of settings or false if none exist
	 */
	public function get_settings() {

		// What are the default settings?
		$defaults = array(
			'show_phrase' => 'showme',
			'hide_phrase' => 'hideme',
		);

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

		// Merge with the defaults
		$site_settings = wp_parse_args( $site_settings, $defaults );

		return $site_settings;
	}

	/**
	 * Return the keycode for a specific phrase.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param	string - the phrase
	 * @return  string|false - the keycode or false if it doesn't exist
	 */
	public function get_phrase_keycode( $phrase ) {

		// Make sure the phrase only has alphanumeric characters
		$phrase = preg_replace( '/[^a-z0-9]/i', '', $phrase );

		// Split phrase into array
		$phrase = str_split( $phrase );

		// Make sure we have a phrase
		if ( empty( $phrase ) ) {
			return false;
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
		return ! empty( $keycode ) ? $keycode : false;

	}

	/**
	 * Return the code for a specific key.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param	string - the key
	 * @return  string|false - the code or false if it doesn't exist
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
		return isset( $keycodes[$key] ) && ! empty( $keycodes[$key] ) ? $keycodes[$key] : false;
	}

	/**
	 * Detects the user's admin bar preference.
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_admin_bar_pref() {
		$this->display_admin_bar = _get_admin_bar_pref();
	}

	/**
	 * Add styles and scripts for our shortcodes.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param	string - $hook_suffix - the ID of the current page
	 * @return	bool - false if didn't enqueue anything
	 */
	public function enqueue_styles_scripts() {

		// Print if no one is logged in OR if the user wants the admin bar
		if ( is_user_logged_in() && ! $this->display_admin_bar ) {
			return false;
		}

		// Get the settings
		$settings = $this->get_settings();

		// Build our data array
		$localize = array();

		// Add 'show_phrase'
		if ( isset( $settings['show_phrase'] ) && ( $show_phrase = $this->get_phrase_keycode( $settings[ 'show_phrase' ] ) ) ) {
			$localize['show_phrase'] = $show_phrase;
		}

		// Add 'hide_phrase'
		if ( isset( $settings['hide_phrase'] ) && ( $hide_phrase = $this->get_phrase_keycode( $settings[ 'hide_phrase' ] ) ) ) {
			$localize['hide_phrase'] = $hide_phrase;
		}

		// Enqueue the script
		wp_enqueue_script( 'show-me-the-admin', trailingslashit( plugin_dir_url( __FILE__ ) . 'js' ) . 'show-me-the-admin.min.js', array( 'jquery' ), SHOW_ME_THE_ADMIN_VERSION, true );

		// Pass some data
		wp_localize_script( 'show-me-the-admin', 'show_me_the_admin', $localize );

	}

	/**
	 * Add styles and scripts to the <head>
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function add_styles_scripts_to_head() {

		// Print if no one is logged in OR if the user wants the admin bar
		if ( is_user_logged_in() && ! $this->display_admin_bar ) {
			return false;
		}

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
	 * Add styles and scripts to the <head>
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function print_login_button() {

		// Don't print if the user is logged in or the admin bar is showing
		if ( is_user_logged_in() || is_admin_bar_showing() ) {
			return;
		}

		// Print the login button with redirect
		$redirect = isset( $_SERVER[ 'REQUEST_URI'] ) ? $_SERVER[ 'REQUEST_URI'] : null;
		?><a id="show-me-the-admin-login" href="<?php echo wp_login_url( site_url( $redirect ) ); ?>">Login to WordPress</a><?php

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