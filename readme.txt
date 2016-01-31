=== Plugin Name ===
Contributors: bamadesigner
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ZCAN2UX7QHZPL&lc=US&item_name=Rachel%20Carden%20%28Show%20Me%20The%20Admin%29&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted
Tags: admin, admin bar, adminbar, toolbar, bar, login, show, hide
Requires at least: 3.0
Tested up to: 4.4.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Hides your admin toolbar and enables you to make it appear, and disappear, by typing a specific phrase.

== Description ==

The admin bar makes it really easy to move back and forth between viewing your site and editing your site but sometimes the toolbar itself can be intrusive. When you need it, you need it, but sometimes you wish it would go away, especially when testing out different designs.

"Show Me The Admin" hides your toolbar and enables you to make it appear, and disappear, at will by typing a specific phrase.

By default, type 'showme' to show the admin bar and 'hideme' to hide it. These phrases can be changed in your settings.

**Show Me The Admin is also multisite-friendly.**

Your "Show Toolbar when viewing site" profile setting must be enabled. If not logged in, a login button will drop down.

== Installation ==

1. Upload 'show-me-the-admin' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Show Me The Admin

== Changelog ==

= 1.0.0 =
Plugin launch

== Filters ==

Show Me The Admin has filters setup to allow you to tweak the plugin.

= Filter the settings =
`/**
 * Filters the "Show Me The Admin" settings.
 *
 * @param   array - $settings - the original settings
 * @return  array - the filtered settings
 */
add_filter( 'show_me_the_admin_settings', 'filter_show_me_the_admin_settings' );
function filter_show_me_the_admin_settings( $settings ) {

    // Change the settings

    // For example, change the phrase to show the admin bar, default is 'showme'
    $settings[ 'show_phrase' ] = 'hello';

    // Or change the phrase to hide the admin bar, default is 'hideme'
    $settings[ 'hide_phrase' ] = 'goodbye';

    // Return the settings
    return $settings;
}`

= Filter the text for the dropdown login button =
`/**
 * Filters the text for the "Show Me The Admin"
 * dropdown login button.
 *
 * @param   string - $text - the original text
 * @return  string - the filtered text
 */
add_filter( 'show_me_the_admin_login_text', 'filter_show_me_the_admin_login_text' );
function filter_show_me_the_admin_login_text( $text ) {

 // Change the text, default is 'Login to WordPress'
 $text = 'Login to the admin';

 // Return the text
 return $text;
}`