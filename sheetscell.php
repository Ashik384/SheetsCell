<?php

/**
 * @wordpress-plugin
 * Plugin Name:       SheetsCell - Get Your Google Sheet Specific Cell data
 * Plugin URI:        https://www.linkedin.com/in/ashikul-islam-ashik-a61479142/
 * Description:       A simple plugin that help you to Display Google Sheets Spacific Cell data in wordpress website using shortcodes. You can use the shortcode in pages and posts in your website. simply you will have to add google api key and google sheets ID.
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

        define( 'SHEETSCELL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
        define( 'SHEETSCELL_PLUGIN_VERSION', '1.0.1' );

        add_shortcode( 'sheets_cell', array( $this, 'sheetscell_shortcode_callback' ) );
        add_action( 'admin_menu', array( $this, 'add_options_page' ) );
        add_action( 'admin_menu', array( $this, 'sheetscell_register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'sheetscell_admin_scripts' ) );
        add_action( 'admin_init', array( $this, 'my_custom_settings_save' ) );
    }

    //enqueue style
    public function sheetscell_admin_scripts() {
        wp_enqueue_style( 'sheetscell-style', SHEETSCELL_PLUGIN_URL . 'assets/admin/css/style.css', array(), SHEETSCELL_PLUGIN_VERSION );
    }

    //Add the options page to the WordPress admin menu
    public function add_options_page() {
        add_options_page(
            __( 'SheetsCell Settings', 'sheetscell' ),
            'SheetsCell',
            'manage_options',
            'google-sheetscell',
            array( $this, 'sheetscell_option_callback' )
        );
    }

    public function sheetscell_option_callback() {?>
        <div class="wrap">
            <h2><?php echo esc_html( __( 'SheetsCell Settings Page', 'sheetscell' ) ); ?></h2>
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
            array( $this, 'google_api_key_field' ),
            'sheetscell_input_settings_section',
            'sheetscell_settings_section'
        );

        // Register Sheets Id
        add_settings_field(
            'google_sheets_id',
            __( 'Google Spreadsheets ID', 'sheetscell' ),
            array( $this, 'spreadsheet_input_field' ),
            'sheetscell_input_settings_section',
            'sheetscell_settings_section'
        );
        
        // New Data Updated
        add_settings_field(
            'sheets_new_data_updated',
            __( 'Sheets New Data Updated?', 'sheetscell' ),
            array( $this, 'sheets_new_data_updated_input' ),
            'sheetscell_input_settings_section',
            'sheetscell_settings_section'
        );
    }

    public function sheetscell_settings_page_info() {
        echo __( 'Dont Have Google API KEY? - <a target="_blank" href="https://console.cloud.google.com/"> Google Console </a>', 'sheetscell' );
        echo "<br/>";
        echo __( 'Dont Have Google Sheets? - <a target="_blank" href="https://docs.google.com/spreadsheets/u/0/"> Google Sheets </a>', 'sheetscell' );
    }

    //Google Key Field
    public function google_api_key_field() {
        $options = get_option( 'sheetscell_option_settings' );
        $google_key_input_value = isset( $options['google_api_key'] ) ? esc_attr( $options['google_api_key'] ) : '';
        echo '<input type="text" class="sc_input_field" name="sheetscell_option_settings[google_api_key]" value="' . $google_key_input_value . '" />';
    }

    //Spreadsheet ID Input
    public function spreadsheet_input_field() {
        $options          = get_option( 'sheetscell_option_settings' );
        $google_sheets_id = isset( $options['google_sheets_id'] ) ? esc_attr( $options['google_sheets_id'] ) : '';
        echo '<input type="text" class="sc_input_field" name="sheetscell_option_settings[google_sheets_id]" value="' . $google_sheets_id . '" />';
    }
    
    //Spreadsheet ID Input
    public function sheets_new_data_updated_input($sheets_new_data_input) {
        $options = get_option( 'sheetscell_option_settings' );
        $sheets_new_data_input = isset( $options['sheets_new_data_updated'] ) ? esc_attr( $options['sheets_new_data_updated'] ) : '';
        echo '<label for="sheetscell_option_settings[sheets_new_data_updated]">';
        echo '<input type="checkbox" class="sheetscell_option_settings[sheets_new_data_updated]" name="sheetscell_option_settings[sheets_new_data_updated]" value="1" ' . checked( $sheets_new_data_input, 1, false ) . '/>';
        echo 'Sheets New Data Updated ?';
        echo '</label>';
    }

    function my_custom_settings_save() {
        if ( isset( $_POST['sheets_new_data_updated'] ) && $_POST['sheets_new_data_updated'] == 1 ) {
            update_option( 'sheets_new_data_updated', 1 );
        } else {
            update_option( 'sheets_new_data_updated', 0 );
        }
    }


    /**
     * Function to genarate shortcode
     *
     * @param [type] $atts
     * @return void
     */
    function sheetscell_shortcode_callback( $atts ) {
        ob_start();
        $atts = shortcode_atts( [
            'cell_id' => 'Sheet1!A1',
        ], $atts );
        
        $options = get_option( 'sheetscell_option_settings' );
        $new_data_updated = isset( $options['sheets_new_data_updated'] ) ? $options['sheets_new_data_updated'] : '';
        //var_dump($new_data_updated);

        $cell_value = '';
        $options    = get_option( 'sheetscell_option_settings' );
        //Google API key
        $google_api_data = isset( $options['google_api_key'] ) ? ltrim( $options['google_api_key'] ) : '';
        //Google Sheets ID
        $sheets_id_data = isset( $options['google_sheets_id'] ) ? ltrim( $options['google_sheets_id'] ) : '';
    
        if ( isset( $google_api_data ) && ! empty( $google_api_data ) && isset( $sheets_id_data ) && ! empty( $sheets_id_data ) ) {
            $api_key     = esc_attr( $google_api_data );
            $location    = $atts['cell_id'];
            $sheets_cell_transiet = 'sheetscell_' . md5( $sheets_id_data . '_' . $location );
            $data        = get_transient( $sheets_cell_transiet );
    
            if ( false === $data ) {
                $sheets_url  = "https://sheets.googleapis.com/v4/spreadsheets/$sheets_id_data/values/$location?&key=$api_key";
                $request     = wp_remote_get( $sheets_url );
                $wp_response = wp_remote_retrieve_response_code( $request );
    
                if ( 404 === $wp_response || 403 === $wp_response ) {
                    echo esc_html( __( "Enter Valid Google Key and Sheets ID", "sheetscell" ) );
                } else {
                    $json_body = json_decode( $request['body'], true );
                    if ( isset( $json_body["error"] ) ) {
                        $error = $json_body["error"];
                    } else {
                        // No error occurred
                        // ...
                    }
    
                    if ( isset( $error["status"] ) && $error["status"] == "INVALID_ARGUMENT" ) {
                        echo $error["message"];
                    } else {
                        if ( isset( $json_body["values"][0][0] ) ) {
                            $cell_value = $json_body["values"][0][0];
                        } else {
                            echo __( 'Empty Cell!', 'sheetscell' );
                        }
    
                    }
                    // Store the data in transient for 30 seconds
                    if( $new_data_updated === 1 ){
                        set_transient( $sheets_cell_transiet, $cell_value, $new_data_updated ); // Transient will expire after 30 seconds
                        ++$new_data_updated;
                    }else{
                        set_transient( $sheets_cell_transiet, $cell_value, 600 );
                    }
                    
                }
            } else {
                $cell_value = $data;
            }
        } else {
            echo __( "Empty Field! Please ensure that you have entered a valid Google key and Sheets ID", "sheetscell" );
        }
    
        $cell_value .= ob_get_clean();
        return $cell_value;
    }
    
}

$SheetsCell = new SheetsCell();
