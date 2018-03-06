# Show Me The Admin

The WordPress toolbar makes it really easy to move between viewing your site and editing your site but sometimes the toolbar itself can be intrusive.

"Show Me The Admin" is a WordPress plugin [in the WordPress repository](https://wordpress.org/plugins/show-me-the-admin/) that hides your toolbar and enables you to make it appear, and disappear, using a variety of methods.

## Features include:
* Hide your toolbar and make it appear by typing a phrase
* Hide your toolbar and show WordPress button in top left corner to click to appear
* Hide your toolbar and make it appear when mouse hovers near top of window

**Show Me The Admin is also multisite-friendly.**

Your "Show Toolbar when viewing site" profile setting must be enabled.

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

        // For example, change the phrase you type to show the toolbar, default is 'showme'
        $settings[ 'show_phrase' ] = 'hello';

        // Or change the phrase you type to hide the toolbar, default is 'hideme'
        $settings[ 'hide_phrase' ] = 'goodbye';

        // Return the settings
        return $settings;
    }

### Filter the phrase to show the toolbar
    /**
     * Filters the phrase to show the toolbar.
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

### Filter the phrase to hide the toolbar
    /**
     * Filters the phrase to hide the toolbar.
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
    
## Development

If you'd like to contribute to the plugin, you will need to clone the repo and setup the following on your local environment:

1. Install dependencies
    * Run `npm install` - [Make sure Node.js and npm are installed](https://docs.npmjs.com/getting-started/installing-node)
    * Run `composer install` - [Make sure composer is installed](https://getcomposer.org/doc/00-intro.md)
2. Run `gulp`
    * Compiles assets - `gulp compile`
    * Runs tests - `gulp test` 
