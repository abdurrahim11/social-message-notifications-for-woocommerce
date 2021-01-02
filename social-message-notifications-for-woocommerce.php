<?php
/**
 * Plugin Name:       Social Message Notifications for WooCommerce
 * Plugin URI:        http://joydevs.com/
 * Description:       Sends whatsapp SMS notifications to your clients for order status changes. You can also receive an SMS message when a new order is received.
 * Version:           1.00
 * Author:            Abdur Rahim
 * Author URI:        https://joydevs.com/
 * License:           GPL v2 or later
 * Text Domain:       social-message-notify
 * Domain Path:       /languages/
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// here define plugin path and url
define( 'wmnw_plugin_path', plugin_dir_path( __FILE__ ) );
define( 'wmnw_plugin_url', plugin_dir_url( __FILE__ ) );
define( 'wmnw_version', '1.00' );
define( 'wmnw_prefix', 'wmnw_' );


// Check if WooCommerce is active
if ( ! function_exists( 'is_plugin_active' ) ) {
    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}

if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) )
{
    add_action( 'admin_notices', 'wmnw_woocommerce_missing' );
    return;
}

function wmnw_woocommerce_missing() {
    $translators_text = sprintf( '%s <a href="https://woocommerce.com/" target="_blank">%s</a> here.', __( "WooCommerce WhatsApp Message Notifications requires WooCommerce to be installed and active. You can download", "social-message-notify" ), __( "WooCommerce", "social-message-notify" ) );
    /* translators: 1. URL link. */
    echo '<div class="error"><p><strong>' . $translators_text . '</strong></p></div>';
}

// plugin init code
if ( ! class_exists( 'WcWhatsAppMessageNotify' ) ){

    class WcWhatsAppMessageNotify {

        function __construct() {
            add_action( 'plugins_loaded' , array( $this, 'plugins_loaded_text_domain' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_script' ) );
            $this->includes();
        }

        public function plugins_loaded_text_domain() {
            load_plugin_textdomain( 'social-message-notify', false, wmnw_plugin_path . 'languages/' );
        }

        public function enqueue_script() {
            wp_enqueue_style( 'wmnw-notify-backend',wmnw_plugin_url . 'assets/css/backend.css',  '', wmnw_version );
            wp_enqueue_script( 'wmnw-notify-backend', wmnw_plugin_url . 'assets/js/backend.js', array( 'jquery' ), wmnw_version, true);
        }

        public function includes() {

            if ( is_admin() ) {
                require_once wmnw_plugin_path . 'includes/class-wmnw-admin-menu.php';
            }

            require_once wmnw_plugin_path . 'includes/class-wmnw-sent-massages.php';
        }

    }

    new WcWhatsAppMessageNotify();
}




