<?php

/**
 * @wordpress-plugin
 * Plugin Name:       SheetsCell - Google Sheets SpecificCell data
 * Plugin URI:        https://www.linkedin.com/in/ashikul-islam-ashik-a61479142/
 * Description:       A simple plugin that help you to Display Google Sheets Spacific Cell data in wordpress website using shortcodes. You can use the shortcode in pages and posts in your website.
 * Version:           1.0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Ashik Ul Islam
 * Author URI:        https://www.linkedin.com/in/ashikul-islam-ashik-a61479142/
 * Text Domain:       sheetscell
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

class SheetsCell {

    //Construct function
    public function __construct() {
        //shortcode
        add_shortcode( 'sheets_cell', array( $this, 'sheetscell_shortcode_func' ) );
        add_action( 'admin_menu', array( $this, 'add_options_page' ) );
        add_action( 'admin_menu', array( $this, 'sheetscell_register_settings' ) );
    }

    public function add_options_page() {
        // Add the options page to the WordPress admin menu
        add_options_page(
            'Sheets Cells Settings',
            'Google SheetsCell',
            'manage_options',
            'google-sheetscell',
            array( $this, 'sheetscell_option_callback' )
        );
    }

    public function sheetscell_option_callback() {?>
        <div class="wrap">
            <h2>SheetsCell Settings</h2>
            <form method="post" action="options.php">
                <?php
                    //Output the settings fields
                    settings_fields( 'myplugin_settings' );
                    do_settings_sections( 'myplugin_settings' );
                    submit_button();
                ?>
            </form>
        </div>
    <?php }

    
    public function sheetscell_register_settings() {
        // Register the plugin's settings
        register_setting(
            'myplugin_settings',
            'myplugin_settings',
            array( $this, 'sanitize_settings' )
        );
        
        // Register a new settings section
        add_settings_section(
            'myplugin_general',
            'General Settings',
            array( $this, 'output_general_section' ),
            'myplugin_settings'
        );

        // Register google key field
        add_settings_field(
            'google_api_key',
            'API Key',
            array( $this, 'sheetscell_google_key' ),
            'myplugin_settings',
            'myplugin_general'
        );

        // Register Sheets Id
        add_settings_field(
            'sheets_id',
            'Sheets ID',
            array( $this, 'sheetscell_sheets_id' ),
            'myplugin_settings',
            'myplugin_general'
        );
    }

    public function output_general_section() {
        echo __("These are the general settings for My Plugin.", "sheetscell");
    }

    // Method to output for google key
    public function sheetscell_google_key() {
        $options = get_option( 'myplugin_settings' );
        if ( isset( $options['google_api_key'] ) ) {
            $google_key_input_value = esc_attr( $options['google_api_key'] );
        } else {
            $google_key_input_value = "";
        }
        echo '<input type="text" name="myplugin_settings[google_api_key]" value="' . $google_key_input_value . '" />';
    }

    // Method to output for Google Sheets
    public function sheetscell_sheets_id() {
        $options = get_option( 'myplugin_settings' );
        if ( isset( $options['sheets_id'] ) ) {
            $google_sheets_id = esc_attr( $options['sheets_id'] );
        } else {
            $google_sheets_id = "";
        }
        echo '<input type="text" name="myplugin_settings[sheets_id]" value="' . $google_sheets_id . '" />';
    }


    /**
     * Function to genarate shortcode
     *
     * @param [type] $atts
     * @return void
     */
    public function sheetscell_shortcode_func( $atts ) {
        $options = get_option( 'myplugin_settings' );
        //Google API key
        $apiInputData = $options['google_api_key'];
        //Google Sheets ID
        $sheets_id = $options['sheets_id'];

        if( $apiInputData == '' && $sheets_id == '' ){
            echo "Looks Both Empty!";
        } else{
            //$API = 'AIzaSyBvT04d04wLj1QCwj3yS-ElJd-U3xEgk_Y';
            $API = "{$apiInputData}";
            //$google_spreadsheet_ID = '1SyAeH3Hl7XMvPzE-BUXqW86BvGYPxgtm3VjIi4nx5bM';
            $google_spreadsheet_ID = "{$sheets_id}";
            $api_key  = esc_attr( $API );
            $location = $atts['cell_id'];
            $get_cell = new WP_Http();
            $cell_url = "https://sheets.googleapis.com/v4/spreadsheets/$google_spreadsheet_ID/values/$location?&key=$api_key";
            $request  = wp_remote_get( $cell_url );
            $wp_response = wp_remote_retrieve_response_code( $request );

            if ( 404 === $wp_response ) {
                echo "Sheets ID Error Genarated";
            } else {
                $cell_response = $get_cell->get( $cell_url );
                $json_body     = json_decode( $cell_response['body'], true );

                if ( isset( $json_body["error"] ) ) {
                    $error = $json_body["error"];
                } else {
                    // No error occurred
                    // ...
                }

                if ( isset( $error["status"] ) && $error["status"] == "INVALID_ARGUMENT" ) {
                    echo $error["message"];
                } else {
                    $cell_value = $json_body["values"][0][0];
                    return $cell_value;
                }
            }
        }

    }
}

$SheetsCell = new SheetsCell();

?>