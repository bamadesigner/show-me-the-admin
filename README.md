# Show Me The Admin

The WordPress admin bar makes it really easy to move back and forth between viewing your site and editing your site but sometimes the toolbar itself can be intrusive. When you need it, you need it, but sometimes you wish it would go away, especially when testing out different designs.

"Show Me The Admin" is a WordPress plugin [in the WordPress repository](https://wordpress.org/plugins/show-me-the-admin/) that hides your toolbar and enables you to make it appear, and disappear, using a variety of methods.

**Features include:**

* Hide your toolbar and make it appear by typing a phrase
* Hide your toolbar and place WordPress button in top left corner to click to appear
* Hide your toolbar and make it appear when mouse hovers near top of window

**Show Me The Admin is also multisite-friendly.**

Your "Show Toolbar when viewing site" profile setting must be enabled. If not logged in, a login button will drop down.

## Filters

Show Me The Admin has filters setup to allow you to tweak the plugin.

### Filter the settings
    /**
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
    }

### Filter the phrase to show the admin bar
    /**
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
    }

### Filter the phrase to hide the admin bar
    /**
     * Filters the phrase to hide the admin bar.
     *
     * @param   string - $hide_phrase - the original phrase
     * @return  string - the filtered phrase
     */
    add_filter( 'show_me_the_admin_hide_phrase', 'filter_show_me_the_admin_hide_phrase' );
    function filter_show_me_the_admin_hide_phrase( $hide_phrase ) {

        // Change the phrase, default is 'hideme'
        $hide_phrase = 'hello';

        // Return the phrase
        return $hide_phrase;
    }

### Filter the text for the dropdown login button
    /**
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
    }