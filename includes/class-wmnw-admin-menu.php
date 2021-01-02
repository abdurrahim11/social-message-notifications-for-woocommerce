<?php
class wmnwAdminMenu {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'create_menu_page' ) );
        add_action( 'admin_post_wmnw_general_setting', array( $this, 'save_data' ) );
        add_action( 'admin_post_wmnw_api_setting', array( $this, 'api_store' ) );
        add_filter( 'plugin_action_links_social-message-notifications-for-woocommerce/social-message-notifications-for-woocommerce.php', array( $this, 'plugin_setting_link') );
    }
    
    public function plugin_setting_link($link) {
        $new_link = sprintf( "<a href='%s'>%s</a>","admin.php?page=social-message-notify",__( "Setting","social-message-notify" ) );
        $link[]   = $new_link;
        return $link;
    }
    
    public function create_menu_page() {
        $page_title  =   __( 'WhatsApp Message Notifications', 'social-message-notify' );
        $menu_title  =   __( 'WhatsApp Message Notifications', 'social-message-notify' );
        $capability  =  'manage_options';
        $parent_slug =  'woocommerce';
        $slug        =  'social-message-notify';
        $callback    =   array( $this, "setting_page_contain" );

        add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $slug, $callback );
    }

    public function setting_page_contain() { ?>
        <div class="wrap woocommerce">
            <h2><?php _e( "WooCommerce WhatsApp Message Notifications","social-message-notify" );?></h2>
            <hr>
            <form method="post" action="<?php echo admin_url( 'admin-post.php' )?>" enctype="multipart/form-data">
                <h3></h3>
                <table class="form-table" role="presentation">
                    <tbody>
                    <h3><?php _e( "Api  Credentials","social-message-notify" );?></h3>
                    <div>
                        <p>
                            <?php
                            echo sprintf( '<a href="%s" target="_blank"> %s </a> %s <a target="_blank" href="%s"> %s </a>','https://chatappbot.com/',__( "WhatsApp api","social-message-notify" ), __( "Credentials are required for WhatsApp Message Notifications. The api website link","social-message-notify" ),'https://chatappbot.com/',__( "here.","social-message-notify" ) );
                            ?>
                        </p>
                    </div>
                    <tr>
                        <td scope="row">
                            <label for="api"><strong><?php _e( "Instance","social-message-notify" ); ?></strong></label>
                        </td>
                        <td>
                            <input type="text" class="regular-text" name="instance" value="<?php  echo $this->has_value_status( wmnw_prefix . 'instance' ) === true ?  $this->get_value( wmnw_prefix . 'instance' ) : '' ?>">
                        </td>
                    </tr>
                    <tr>
                        <td scope="row">
                            <label for="api"><strong><?php _e( "Access Token", "social-message-notify" ); ?></strong></label>
                        </td>
                        <td>
                            <input type="text" class="regular-text" name="api" value="<?php  echo $this->has_value_status( wmnw_prefix . 'api' ) === true ?  $this->get_value( wmnw_prefix . 'api' ) : '' ?> ">
                        </td>
                    </tr>
                    </tbody>
                </table>
                <input type="hidden" name="action" value="wmnw_api_setting">
                <?php wp_nonce_field( "wmnw_general_setting_nonce" )?>
                <?php submit_button( __("Save Settings", "social-message-notify"), "primary", "general-setting-submit" ); ?>
            </form>
            <hr>
            <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" enctype="multipart/form-data">
                <h3><?php _e( "Admin notification", "social-message-notify" ); ?></h3>
                <div>
                    <p>
                        <?php _e( "Notify store owner about new orders", "social-message-notify" ); ?>
                    </p>
                </div>
                <table class="form-table" role="presentation">
                    <tbody>
                    <?php
                    foreach ( $this->admin_notification_massage() as $key => $value ) {
                        ?>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for=""><?php echo $value[0] ?></label>
                            </th>
                            <td class="forminp wmnw-form-group">
                                <input <?php  echo $this->has_value_status( wmnw_prefix . 'check_' . $key ) === true ?  'checked' : '' ?>  name="<?php echo wmnw_prefix . 'check_' . $key ?>" type="checkbox" value="yes" >
                                <div class="wmnw-filed-input">
                                    <input class="wmnw-filed-show-hide msg-template  <?php  echo $this->has_value_status( wmnw_prefix . 'check_' . $key ) === true ?  '' : 'wmnw-filed-hidden' ?>"  name="<?php echo wmnw_prefix . $key ?>" type="text" size="50" value="<?php echo $value[2] ?>"  required="required">
                                    <br>
                                    <input class="wmnw-filed-show-hide admin-phone msg-template  <?php  echo $this->has_value_status( wmnw_prefix . 'check_' . $key ) == true ?  '' : 'wmnw-filed-hidden' ?>"  name="<?php echo wmnw_prefix . 'admin_phone' ?>" type="text" size="50" value="<?php echo $this->has_value_status( wmnw_prefix . 'admin_phone' ) ? $this->get_value( wmnw_prefix . 'admin_phone' ) : '' ?>" placeholder="Admin phone Number" >
                                    <p class="wmnw-filed-show-hide <?php  echo $this->has_value_status( wmnw_prefix . 'check_' . $key ) === true ?  '' : 'wmnw-filed-hidden' ?>"><?php _e( "You must enter your country code when typing the number", "social-message-notify" );?>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>
                </table>
                <h3><?php _e( "Customer notification", "social-message-notify" ); ?></h3>
                <div>
                    <p>
                        <?php _e( "Send SMS notification to client for each order status change", "social-message-notify" );?>
                    </p>
                </div>
                <table class="form-table" role="presentation">
                    <tbody>
                    <?php
                    foreach ( $this->status_notification_massage() as $key => $value ) {
                        ?>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for=""><?php echo $value[0] ?></label>
                            </th>
                            <td class="forminp wmnw-form-group">
                                <input <?php  echo $this->has_value_status( wmnw_prefix . 'check_' . $key ) == true ?  'checked' : '' ?>  name="<?php echo wmnw_prefix .  'check_' . $key ?>" type="checkbox" value="yes" >
                                <div class="wmnw-filed-input">
                                    <input class="wmnw-filed-show-hide msg-template  <?php  echo $this->has_value_status( wmnw_prefix . 'check_' . $key ) === true ?  '' : 'wmnw-filed-hidden' ?>"  name="<?php echo wmnw_prefix . $key ?>" type="text" size="50" value="<?php echo $value[2] ?>" required="required">
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>
                </table>
                <input type="hidden" name="action" value="wmnw_general_setting">
                <?php wp_nonce_field( 'wmnw_general_setting_nonce' )?>
                <?php submit_button( __("Save Settings", "social-message-notify" ), "primary", "general-setting-submit" );?>
            </form>
            <div>
                <h3><?php _e( "You can use following variables in your templates:", "social-message-notify" )?></h3>
                <hr>
                <?php
                foreach ($this->wc_variables() as $var) {
                    echo ' <code>%' . $var . '%</code>';
                }
                ?>
            </div>
        </div>
        <?php
    }

    public function api_store() {
        check_admin_referer( 'wmnw_general_setting_nonce' );

        if ( isset( $_POST['api'] ) Or isset( $_POST['instance'] ) ) {
            update_option( wmnw_prefix . "api", sanitize_textarea_field( $_POST["api"] ) );
            update_option( wmnw_prefix . "instance", sanitize_textarea_field( $_POST["instance"] ) );
        }

        wp_redirect( "admin.php?page=social-message-notify" );
    }

    public function save_data() {

        check_admin_referer( 'wmnw_general_setting_nonce' );

        if ( $_POST['wmnw_check_admin_msg_new_order'] ) {
            update_option( 'wmnw_check_admin_msg_new_order', sanitize_text_field( $_POST['wmnw_check_admin_msg_new_order'] ) );
        }else{
            update_option( 'wmnw_check_admin_msg_new_order', '' );
        }

        if ( $_POST['wmnw_check_msg_new_order'] ) {
            update_option( 'wmnw_check_msg_new_order' , sanitize_text_field( $_POST['wmnw_check_msg_new_order'] ) );
        }else{
            update_option( 'wmnw_check_msg_new_order', '' );
        }

        if ( $_POST['wmnw_check_msg_on_hold'] ) {
            update_option( 'wmnw_check_msg_on_hold', sanitize_text_field( $_POST['wmnw_check_msg_on_hold'] ) );
        }else{
            update_option( 'wmnw_check_msg_on_hold', '' );
        }

        if ( $_POST['wmnw_check_msg_processing'] ) {
            update_option( 'wmnw_check_msg_processing', sanitize_text_field( $_POST['wmnw_check_msg_processing'] ) );
        }else{
            update_option( 'wmnw_check_msg_processing', '' );
        }

        if ( $_POST['wmnw_check_msg_completed'] ) {
            update_option( 'wmnw_check_msg_completed', sanitize_text_field( $_POST['wmnw_check_msg_completed'] ) );
        }else{
            update_option( 'wmnw_check_msg_completed', '' );
        }

        if ( $_POST['wmnw_check_msg_cancelled'] ) {
            update_option( 'wmnw_check_msg_cancelled', sanitize_text_field( $_POST['wmnw_check_msg_cancelled'] ) );
        }else{
            update_option( 'wmnw_check_msg_cancelled','' );
        }

        if ( $_POST['wmnw_check_msg_refunded'] ) {
            update_option( 'wmnw_check_msg_refunded', sanitize_text_field( $_POST['wmnw_check_msg_refunded'] ) );
        }else{
            update_option( 'wmnw_check_msg_refunded', '' );
        }

        if ( $_POST['wmnw_check_msg_failure'] ) {
            update_option( 'wmnw_check_msg_failure', sanitize_text_field( $_POST['wmnw_check_msg_failure'] ) );
        }else{
            update_option('wmnw_check_msg_failure','');
        }

        if ( $_POST['wmnw_check_msg_custom'] ) {
            update_option( 'wmnw_check_msg_custom', sanitize_text_field( $_POST['wmnw_check_msg_custom'] ) );
        }else{
            update_option( 'wmnw_check_msg_custom', '' );
        }

        foreach ( $_POST as $key => $row ) {
            if ( ! preg_match( "/check/i" , $row ) ) {
                $value =  sanitize_text_field( $row );
                update_option( $key, $value );
            }
        }

        wp_redirect( "admin.php?page=social-message-notify" );
    }

    public function status_notification_massage() {
        $massage = array(
            'msg_new_order' => array(
                __( 'New Order message', 'social-message-notify' ),
                __( 'Message sent to you on receipt of a new order', 'social-message-notify' ),
                $this->has_value_status( wmnw_prefix . 'msg_new_order' ) === true ?  $this->get_value( wmnw_prefix . 'msg_new_order' ) : "Order %id% has been received on %shop_name%."
            ),
            'msg_pending' => array(
                __( 'Pending Payment message', 'social-message-notify' ),
                __( 'Message sent to the client when a new order is awaiting payment', 'social-message-notify' ),
                $this->has_value_status( wmnw_prefix . 'msg_pending' ) === true ?  $this->get_value( wmnw_prefix . 'msg_pending' ) : "Dear %billing_first_name%, your order on %shop_name% is awaiting payment. %signature%"
            ),
            'msg_on_hold' => array(
                __( 'On-Hold message', 'social-message-notify' ),
                __( 'Message sent to the client when an order goes on-hold', 'social-message-notify' ),
                $this->has_value_status( wmnw_prefix . 'msg_on_hold' ) === true ?  $this->get_value( wmnw_prefix . 'msg_on_hold' ) : "Dear %billing_first_name%, your order %id% on %shop_name% is on-hold. %signature%"
            ),
            'msg_processing' => array(
                __( 'Order Processing message', 'social-message-notify' ),
                __( 'Message sent to the client when an order is under process', 'social-message-notify' ),
                $this->has_value_status( wmnw_prefix . 'msg_processing' ) === true ?  $this->get_value( wmnw_prefix . 'msg_processing' ) :  "Dear %billing_first_name%, your order %id% on %shop_name% is being processed. %signature%"
            ),
            'msg_completed' => array(
                __( 'Order Completed message', 'social-message-notify' ),
                __( 'Message sent to the client when an order is completed', 'social-message-notify' ),
                $this->has_value_status( wmnw_prefix . 'msg_completed' ) === true ?  $this->get_value( wmnw_prefix . 'msg_completed' ) :  "Dear %billing_first_name%, your order %id% on %shop_name% has been completed. %signature%"
            ),
            'msg_cancelled' => array(
                __( 'Order Cancelled message', 'social-message-notify' ),
                __( 'Message sent to the client when an order is cancelled', 'social-message-notify' ),
                $this->has_value_status( wmnw_prefix . 'msg_cancelled' ) === true ?  $this->get_value( wmnw_prefix . 'msg_cancelled' ) : "Dear %billing_first_name%, your order %id% on %shop_name% has been cancelled. %signature%"
            ),
            'msg_refunded' => array(
                __( 'Payment Refund message', 'social-message-notify' ),
                __( 'Message sent to the client when an order payment is refunded', 'social-message-notify' ),
                $this->has_value_status( wmnw_prefix . 'msg_refunded' ) === true ?  $this->get_value( wmnw_prefix . 'msg_refunded' ) : "Dear %billing_first_name%, payment for your order %id% on %shop_name% has been refunded. It may take a few business days to reflect in your account. %signature%"
            ),
            'msg_failure' => array(
                __( 'Payment Failure message', 'social-message-notify' ),
                __( 'Message sent to the client when a payment fails', 'social-message-notify' ),
                $this->has_value_status( wmnw_prefix . 'msg_failure' ) === true ?  $this->get_value( wmnw_prefix . 'msg_failure' ) : "Dear %billing_first_name%, recent attempt for payment towards your order on %shop_name% has failed. Please retry by visiting order history in My Account section. %signature%"
            ),
            'msg_custom' => array(
                __( 'Custom Status message', 'social-message-notify' ),
                __( 'Message sent to the client when order moves to a custom status (defined by other plugins)', 'social-message-notify' ),
                $this->has_value_status( wmnw_prefix . 'msg_custom') === true ?  $this->get_value(wmnw_prefix . 'msg_custom' ) :  "Dear %billing_first_name%, your order %id% on %shop_name% has been %status%. Please review your order. %signature%"
            )
        );

        return $massage;
    }

    public function admin_notification_massage() {
        $massage = array(
            'admin_msg_new_order' => array(
                __( 'New Order message', 'social-message-notify' ),
                __( 'Message sent to you on receipt of a new order', 'social-message-notify' ),
                $this->has_value_status( wmnw_prefix . 'admin_msg_new_order' ) === true ?  $this->get_value( wmnw_prefix . 'admin_msg_new_order' ) :  "Order %id% has been received on %shop_name%."
            ),
        );

        return $massage;
    }

    public function has_value_status( $value ) {
        $status = get_option( $value );

        if( $status ) {
            return true;
        }else{
            return  false;
        }
    }

    public function get_value( $value ) {
        $value = get_option( $value );
        return $value;
    }

    public function wc_variables() {
        $vars = array( 'id', 'order_key', 'billing_first_name', 'billing_last_name', 'billing_company', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_postcode', 'billing_country', 'billing_state', 'billing_email', 'billing_phone', 'shipping_first_name', 'shipping_last_name', 'shipping_company', 'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_postcode', 'shipping_country', 'shipping_state', 'shipping_method', 'shipping_method_title', 'payment_method', 'payment_method_title', 'order_discount', 'cart_discount', 'order_tax', 'order_shipping', 'order_shipping_tax', 'order_total', 'status', 'prices_Wc\WhatsApplude_tax', 'tax_display_cart', 'display_totals_ex_tax', 'display_cart_ex_tax', 'order_date', 'modified_date', 'customer_message', 'customer_note', 'post_status', 'shop_name', 'order_product' );
        return $vars;
    }
    
}

new wmnwAdminMenu();