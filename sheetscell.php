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

        define( 'sheetscell_plugin_path', plugin_dir_url( __FILE__ ) );
        define( 'sheetscell_plugin_version', '1.0.1' );

        add_shortcode( 'sheets_cell', array( $this, 'sheetscell_shortcode_callback' ) );
        add_action( 'admin_menu', array( $this, 'add_options_page' ) );
        add_action( 'admin_menu', array( $this, 'sheetscell_register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'sheetscell_admin_scripts' ) );
    }

    //enqueue style
    public function sheetscell_admin_scripts() {
        wp_enqueue_style( 'sheetscell-style', sheetscell_plugin_path . 'assets/admin/css/style.css', array(), sheetscell_plugin_version );
    }

    //Add the options page to the WordPress admin menu
    public function add_options_page() {
        add_options_page(
            'SheetsCell Settings',
            'SheetsCell',
            'manage_options',
            'google-sheetscell',
            array( $this, 'sheetscell_option_callback' )
        );
    }

    public function sheetscell_option_callback() {?>
        <div class="wrap">
            <h2><?php echo __( 'SheetsCell Settings Page', 'sheetscell' ) ?></h2>
            <form method="post" action="options.php">
                <?php
                    settings_fields( 'sheetscell_settings_field_group' );
                    do_settings_sections( 'sheetscell_input_settings_section' );
                    submit_button( __( 'Save Settings', 'sheetscell' ), 'primary', 'sheets_save_bttn' );
                    ?>
            </form>
        </div>
    <?php }

    public function sheetscell_register_settings() {
        // Register the plugin's settings
        register_setting(
            'sheetscell_settings_field_group',
            'sheetscell_option_settings',
            array( $this, 'sanitize_settings' )
        );

        // Register a new settings section
        add_settings_section(
            'sheetscell_settings_section',
            __( 'Add Your Information', 'sheetscell' ),
            array( $this, 'sheetscell_settings_page_info' ),
            'sheetscell_input_settings_section'
        );

        // Register google key field
        add_settings_field(
            'google_api_key',
            __( 'Google API Key', 'sheetscell' ),
            array( $this, 'sheetscell_google_key' ),
            'sheetscell_input_settings_section',
            'sheetscell_settings_section'
        );

        // Register Sheets Id
        add_settings_field(
            'google_sheets_id',
            __( 'Google Spreadsheets ID', 'sheetscell' ),
            array( $this, 'sheetscell_sheets_id' ),
            'sheetscell_input_settings_section',
            'sheetscell_settings_section'
        );
    }

    public function sheetscell_settings_page_info() {
        echo __( 'Dont Have Google API KEY? - <a target="_blank" href="https://console.cloud.google.com/welcome?project=future-area-243117"> Google Console </a>', 'sheetscell' );
        echo "<br/>";
        echo __( 'Dont Have Google Sheets? - <a target="_blank" href="https://docs.google.com/spreadsheets/u/0/"> Google Sheets </a>', 'sheetscell' );
    }

    //Google Key Field 
    public function sheetscell_google_key() {
        $options = get_option( 'sheetscell_option_settings' );
        if ( isset( $options['google_api_key'] ) ) {
            $google_key_input_value = esc_attr( $options['google_api_key'] );
        } else {
            $google_key_input_value = "";
        }
        echo '<input type="text" class="sheetscell_input_field" name="sheetscell_option_settings[google_api_key]" value="' . $google_key_input_value . '" />';
    }

    //Spreadsheet ID Input
    public function sheetscell_sheets_id() {
        $options = get_option( 'sheetscell_option_settings' );
        if ( isset( $options['google_sheets_id'] ) ) {
            $google_sheets_id = esc_attr( $options['google_sheets_id'] );
        } else {
            $google_sheets_id = "";
        }
        echo '<input type="text" class="sheetscell_input_field" name="sheetscell_option_settings[google_sheets_id]" value="' . $google_sheets_id . '" />';
    }

    /**
     * Function to genarate shortcode
     *
     * @param [type] $atts
     * @return void
     */
    public function sheetscell_shortcode_callback( $atts ) {
        $options = get_option( 'sheetscell_option_settings' );
        //Google API key
        if ( isset( $options['google_api_key'] ) ) {
            $google_api_data = ltrim( $options['google_api_key'] );
        }
        //Google Sheets ID
        if ( isset( $options['google_sheets_id'] ) ) {
            $sheets_id_data = ltrim( $options['google_sheets_id'] );
        }
        if ( isset( $google_api_data ) && !empty( $google_api_data ) && isset( $sheets_id_data ) && !empty( $sheets_id_data ) ) {
            $api_key     = esc_attr( $google_api_data );
            $location    = $atts['cell_id'];
            $get_cell    = new WP_Http();
            $sheets_url  = "https://sheets.googleapis.com/v4/spreadsheets/$sheets_id_data/values/$location?&key=$api_key";
            $request     = wp_remote_get( $sheets_url );
            $wp_response = wp_remote_retrieve_response_code( $request );

            if ( 404 === $wp_response || 403 === $wp_response ) {
                echo __( "Enter Vallied Google Key and Sheets ID", "sheetscell" );
            } else {
                $cell_response = $get_cell->get( $sheets_url );
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
        } else {
            echo __( "Empty Field! Please ensure that you have entered a valid Google key and Sheets ID", "sheetscell" );
        }

    }
}

$SheetsCell = new SheetsCell();

?>