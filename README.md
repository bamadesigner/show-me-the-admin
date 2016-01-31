# Show Me The Admin

The WordPress admin bar makes it really easy to move back and forth between viewing your site and editing your site but sometimes the toolbar itself can be intrusive. When you need it, you need it, but sometimes you wish it would go away, especially when testing out different designs.

"Show Me The Admin" is a WordPress plugin that hides your toolbar and enables you to make it appear, and disappear, at will by typing a specific phrase.

By default, type 'showme' to show the admin bar and 'hideme' to hide it. These phrases can be changed in your settings.

**Show Me The Admin is also multisite-friendly.**

Your "Show Toolbar when viewing site" profile setting must be enabled. If not logged in, a login button will drop down.

## Filters

Show Me The Admin has filters setup to allow you to tweak the plugin.

### Filter the settings
    /**
     * Filters the "Show Me The Admin" settings.
     *
     * @param   array - the original settings
     * @return  array - the filtered settings
     */
    add_filter( 'show_me_the_admin_settings', 'filter_show_me_the_admin_settings' );
    function filter_show_me_the_admin_settings( $settings ) {

        // Change the settings

        // Return the settings
        return $settings;
    }