=== Plugin Name ===
Contributors: bamadesigner
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ZCAN2UX7QHZPL&lc=US&item_name=Rachel%20Carden%20%28Show%20Me%20The%20Admin%29&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted
Tags: admin, admin bar, adminbar, toolbar, bar, login, show, hide
Requires at least: 3.0
Tested up to: 4.4.2
Stable tag: 1.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Hides your admin toolbar and enables you to make it appear, and disappear, using a variety of methods.

== Description ==

The WordPress admin bar makes it really easy to move between viewing your site and editing your site but sometimes the toolbar itself can be intrusive.

"Show Me The Admin" is a WordPress plugin that hides your toolbar and enables you to make it appear, and disappear, using a variety of methods.

= Features include: =
* Hide your toolbar and make it appear by typing a phrase
* Hide your toolbar and show WordPress button in top left corner to click to appear
* Hide your toolbar and make it appear when mouse hovers near top of window

**Show Me The Admin is also multisite-friendly.** Please use the [Show Me The Admin GitHub repo](https://github.com/bamadesigner/show-me-the-admin) to contribute, submit issues, and suggest features.

Your "Show Toolbar when viewing site" profile setting must be enabled. If not logged in, a login button will drop down.

== Installation ==

1. Upload 'show-me-the-admin' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Show Me The Admin

== Changelog ==

= 1.0.2 =
* CHECK YOUR SETTINGS - I modified the settings so users can enable/disable not logged in functionality for each feature
* Optimized/cleaned up the settings

= 1.0.1 =
* Removed margin top change when admin bar slides down to decrease conflicts with themes
* Now removes the <body> admin-bar CSS to help get rid of a theme's conflicting styles

= 1.0.0 =
Plugin launch

== Upgrade Notice ==

= 1.0.2 =
* CHECK YOUR SETTINGS - I modified the settings so users can enable/disable not logged in functionality for each feature
* Optimized/cleaned up the settings

= 1.0.1 =
* Removed margin top change when admin bar slides down to decrease conflicts with themes
* Now removes the <body> admin-bar CSS to help get rid of a theme's conflicting styles

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

    // For example, change the phrase you type to show the admin bar, default is 'showme'
    $settings[ 'show_phrase' ] = 'hello';

    // Or change the phrase you type to hide the admin bar, default is 'hideme'
    $settings[ 'hide_phrase' ] = 'goodbye';

    // Return the settings
    return $settings;
}`

= Filter the phrase to show the admin bar =
`/**
 * Filters the phrase to show the admin bar.
 *
 * @param   string - $show_phrase - the original phrase
 * @return  string - the filtered phrase
 */
add_filter( 'show_me_the_admin_show_phrase', 'filter_show_me_the_admin_show_phrase' );
function filter_show_me_the_admin_show_phrase( $show_phrase ) {

    // Change the phrase, default is 'showme'
    $show_phrase = 'hello';

    // Return the phrase
    return $show_phrase;
}`

= Filter the phrase to hide the admin bar =
`/**
 * Filters the phrase to hide the admin bar.
 *
 * @param   string - $hide_phrase - the original phrase
 * @return  string - the filtered phrase
 */
add_filter( 'show_me_the_admin_hide_phrase', 'filter_show_me_the_admin_hide_phrase' );
function filter_show_me_the_admin_hide_phrase( $hide_phrase ) {

    // Change the phrase, default is 'hideme'
    $hide_phrase = 'goodbye';

    // Return the phrase
    return $hide_phrase;
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