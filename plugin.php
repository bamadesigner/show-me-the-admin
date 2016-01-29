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
	 * Detects the user's admin bar preference.
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function get_admin_bar_pref() {
		$this->display_admin_bar = _get_admin_bar_pref();
	}

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
	 * Returns our settings.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param	boolean - $network - whether or not to retrieve network settings
	 * @return  array|false - array of settings or false if none exist
	 */
	public function get_settings( $network = false ) {
		return $network ? get_site_option( 'show_me_the_admin', array() ) : get_option( 'show_me_the_admin', array() );
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

		// Enqueue the script
		wp_enqueue_script( 'show-me-the-admin', trailingslashit( plugin_dir_url( __FILE__ ) . 'js' ) . 'show-me-the-admin.min.js', array( 'jquery' ), SHOW_ME_THE_ADMIN_VERSION, true );

		// Build out our show phrase - default is 'showme'
		$show_phrase = '837279877769';

		// Build out our hide phrase - default is 'hideme'
		$hide_phrase = '727368697769';

		// @TODO pull from settings

		// Pass some data
		wp_localize_script( 'show-me-the-admin', 'show_me_the_admin', array(
			'show_phrase' => $show_phrase,
			'hide_phrase' => $hide_phrase,
		));

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