<?php
/**
 * Plugin Name: Yoast Bulk Seo update
 * Description: Updates Yoast SEO's title and description from Google Spreadsheet
 * Author: Hemnath Mouli
 * Version: 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'YBSU_DIR', dirname( __FILE__ ) );

function ybsu_add_settings() {
    add_options_page( 'Yoast Bulk Update', 'Yoast SEO Update', 'manage_options', 'YBSU', 'ybsu_setting_page');
}

add_action( 'admin_menu', 'ybsu_add_settings' );

function ybsu_setting_page() {
    include YBSU_DIR . '/utils/settings.php';
}

function ybsu_update( $name = '', $post_id = 0, $value = '' ) {
    if ( $name != '' && $post_id && $value != "" ) {
        return update_post_meta( $post_id, $name, $value );
    }
    return false;
}
?>
