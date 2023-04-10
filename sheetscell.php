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
        //add_action( 'admin_menu', array( $this, 'sheetscell_register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'sheetscell_admin_scripts' ) );
        add_action( 'admin_init', array( $this, 'sheetscell_option_save' ) );
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

    function sheetscell_get_options() {
        $options = get_option( 'sheetscell_option_settings' );
        if ( empty( $options ) ) {
            $options = array( 'google_api_key' => '', 'google_sheets_id' => '', 'sheets_caching_time' => '' );
        }
        return $options;
    }

    public function sheetscell_option_callback() {
        $get_option_data = $this->sheetscell_get_options();
        // Access the returned options data
        $google_api_key      = esc_attr( $get_option_data['google_api_key'] );
        $google_sheets_id    = esc_attr( $get_option_data['google_sheets_id'] );
        $sheets_caching_time = esc_attr( $get_option_data['sheets_caching_time'] );

        ?>
        <div class="wrap">
            <h2><?php echo esc_html( __( 'SheetsCell Settings Page', 'sheetscell' ) ); ?></h2>

            <form method="post" action="" id="sheetscell_option_form">
            <input type="hidden" name="action" id="" value="sheetscell_option_save">

            <h2><?php echo esc_html( __( 'Add Your Information', 'sheetscell' ) ); ?></h2>

            Dont Have Google API KEY? - <a target="_blank" href="https://console.cloud.google.com/"> Google Console </a><br>Dont Have Google Sheets? - <a target="_blank" href="https://docs.google.com/spreadsheets/u/0/"> Google Sheets </a>
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">Google API Key</th>
                        <td><input type="text" class="sc_input_field" name="sheetscell_option_settings[google_api_key]" value="<?php echo $google_api_key; ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row">Google Spreadsheets ID</th>
                        <td><input type="text" class="sc_input_field" name="sheetscell_option_settings[google_sheets_id]" value="<?php echo $google_sheets_id; ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row">Set caching time</th>
                        <td>
                        <input type="text" class="sc_input_field" name="sheetscell_option_settings[sheets_caching_time]" value="<?php echo $sheets_caching_time; ?>">
                        <p style="color:red"> After Updated google sheet make make a save to show instant data or it will show when caching time expired!  </p>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="submit"><input type="submit" name="sheets_save_bttn" id="sheets_save_bttn" class="button button-primary" value="Save Settings"></p>
            </form>
        </div>
    <?php }

    // Function to update data and clear transient catch time
    function sheetscell_option_save() {

        if ( isset( $_POST['action'] ) && $_POST['action'] == 'sheetscell_option_save' ) {

            $sheetscell_options = $_POST['sheetscell_option_settings'];
            $sheetscell_options = array_map( 'sanitize_text_field', $sheetscell_options );
            $update             = update_option( 'sheetscell_option_settings', $sheetscell_options );
            wp_redirect( admin_url( '/options-general.php?page=google-sheetscell' ) );
            exit;
        }

        global $wpdb;
        // Define the table name
        $table_name = $wpdb->prefix . 'options';
        // Define the SQL query
        $query = "DELETE FROM $table_name WHERE option_name LIKE '%sheetscell_trans%'";
        // Execute the query
        $result = $wpdb->query( $query );

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

        $cell_value = '';

        $get_option_data = $this->sheetscell_get_options();
        // //Google API key
        $google_api_data = isset( $get_option_data['google_api_key'] ) ? ltrim( $get_option_data['google_api_key'] ) : '';
        // //Google Sheets ID
        $sheets_id_data = isset( $get_option_data['google_sheets_id'] ) ? ltrim( $get_option_data['google_sheets_id'] ) : '';
        // // Transiet time
        $catch_time_set = isset( $get_option_data['sheets_caching_time'] ) ? $get_option_data['sheets_caching_time'] : '';

        if ( $catch_time_set ) {
            $catch_time_expired        = floatval( $catch_time_set );
            $catch_time_expired_second = $catch_time_expired * 60 * 60;
            var_dump($catch_time_expired_second);
        }

        if ( isset( $google_api_data ) && !empty( $google_api_data ) && isset( $sheets_id_data ) && !empty( $sheets_id_data ) ) {
            $api_key  = esc_attr( $google_api_data );
            $location = $atts['cell_id'];

            $sheets_cell_transiet = 'sheetscell_trans_' . md5( $sheets_id_data . '_' . $location );
            $data = get_transient( $sheets_cell_transiet );

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
                    // Store the data in transient
                    set_transient( $sheets_cell_transiet, $cell_value, 360000 );
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
