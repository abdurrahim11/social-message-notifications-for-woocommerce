<?php

class wmnwSentMassages{

    function __construct()
    {
        add_action( 'woocommerce_order_status_changed', array( $this, 'process_status' ), 10, 3 );
        add_action( 'woocommerce_new_order', array( $this, 'new_order' ), 20 );
    }

    public function process_status( $order_id, $old_status, $status ){

        $order = new \WC_Order( $order_id );
        $shipping_phone = false;
        $phone = $order->get_billing_phone();

        //Remove old 'wc-' prefix from the order status
        $status = str_replace( 'wc-', '', $status );

        $template = "";

        switch ( $status ) {
            case 'on-hold':
                if ( get_option( wmnw_prefix . 'check_msg_on_hold' ) ) {
                    $massage =  get_option( wmnw_prefix . 'msg_on_hold' );
                    $template = $this->process_variables( $massage, $order );
                }
                break;

            case 'processing':
                if ( get_option( wmnw_prefix . 'check_msg_processing' ) ) {
                    $massage =  get_option( wmnw_prefix . 'msg_processing' );
                    $template = $this->process_variables( $massage, $order );
                }
                break;

            case 'completed':
                if ( get_option( wmnw_prefix . 'check_msg_completed' ) ) {
                    $massage =  get_option( wmnw_prefix . 'msg_completed' );
                    $template = $this->process_variables( $massage, $order );
                }
                break;

            case 'cancelled':
                if ( get_option( wmnw_prefix . 'check_msg_cancelled' ) ) {
                    $massage =  get_option( wmnw_prefix . 'msg_cancelled' );
                    $template = $this->process_variables( $massage, $order );
                }
                break;

            case 'refunded':
                if ( get_option(wmnw_prefix . 'check_msg_refunded' ) ) {
                    $massage =  get_option( wmnw_prefix . 'msg_refunded' );
                    $template = $this->process_variables( $massage, $order );
                }
                break;

            case 'failed':
                if ( get_option( wmnw_prefix . 'check_msg_failure') ) {
                    $massage =  get_option( wmnw_prefix . 'msg_failure' );
                    $template = $this->process_variables( $massage, $order );
                }
                break;

            default:
                if ( get_option( wmnw_prefix . 'check_msg_custom') ) {
                    $massage =  get_option( wmnw_prefix . 'msg_custom' );
                    $template = $this->process_variables( $massage, $order );
                }
        }

        $phone_process = $this->process_phone( $order, $phone );

        if ( ! empty( $template ) ) {
            $this->whatsapp_massage_sent( $phone_process, $template );
        }

    }

    public function new_order( $order_id ) {

        $order = new \WC_Order( $order_id );
        $phone = $order->get_billing_phone();

        //customer new order massage
        if ( get_option( wmnw_prefix . 'msg_new_order' ) ) {

            $massage =  get_option( wmnw_prefix . 'msg_new_order' );
            $process_massage = $this->process_variables( $massage, $order );
            $phone_process = $this->process_phone( $order, $phone );
            $this->whatsapp_massage_sent( $phone_process, $process_massage );

        }

        //admin new order massage
        if ( get_option( wmnw_prefix . 'check_admin_msg_new_order' ) ) {

            $massage =  get_option( wmnw_prefix . 'admin_msg_new_order' );
            $process_massage = $this->process_variables( $massage, $order );
            $phone = get_option( wmnw_prefix . 'admin_phone' );
            $this->whatsapp_massage_sent( $phone, $process_massage );

        }

    }

    public function whatsapp_massage_sent( $phone, $massage ) {
        // get access token
        $token = get_option( wmnw_prefix . "api" ) ? get_option( wmnw_prefix . "api" ) : '';

        //get instanceId
        $instanceId = get_option( wmnw_prefix . "instance" ) ? get_option( wmnw_prefix . "instance" ) : '';

        $data = array(
            'instance_id' => $instanceId, // Your instance id
            'massage'     => $massage, // Message
            'number'      => $phone, // Receivers phone
            'token_key'   => $token, // Token key
        );

        $query = http_build_query($data);
        // request url
        $url = 'https://chatappbot.com/api/massage?' . $query;
        wp_remote_get($url);
        return ;
    }

    public function process_variables( $message, $order = null, $additional_data = [] ) {

        //template customize variables here
        $sms_strings = array( 'id', 'status', 'prices_include_tax', 'tax_display_cart', 'display_totals_ex_tax', 'display_cart_ex_tax', 'order_date', 'modified_date', 'customer_message', 'customer_note', 'post_status', 'shop_name', 'note', 'order_product' );
        $wc_whatsapp_notify_variables = array( 'order_key', 'billing_first_name', 'billing_last_name', 'billing_company', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_postcode', 'billing_country', 'billing_state', 'billing_email', 'billing_phone', 'shipping_first_name', 'shipping_last_name', 'shipping_company', 'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_postcode', 'shipping_country', 'shipping_state', 'shipping_method', 'shipping_method_title', 'payment_method', 'payment_method_title', 'order_discount', 'cart_discount', 'order_tax', 'order_shipping', 'order_shipping_tax', 'order_total', 'order_currency' );
        $specials = array( 'order_date', 'modified_date', 'shop_name', 'id', 'order_product', 'signature' );

        $order_variables = $order ? get_post_custom( $order->get_id() ) : []; //WooCommerce 2.1
        $custom_variables = explode( "\n", str_replace( array( "\r\n", "\r" ),  "\n", $this->wc_whatsapp_notify_field( 'variables' ) ) );

        //template customize additional variables
        $additional_variables = array_keys( $additional_data );

        if ( empty( $order ) ) {
            $order = new WC_Order();
        }

        //find variable form string
        preg_match_all("/%(.*?)%/", $message,  $search );

        //This will bring out a variable through the loop and extract the data from woocommerce
        foreach ( $search[1] as $variable ) {
            $variable = strtolower( $variable );

            if ( ! in_array( $variable, $sms_strings ) && ! in_array( $variable, $wc_whatsapp_notify_variables ) && ! in_array( $variable, $specials ) && ! in_array( $variable, $custom_variables ) && ! in_array( $variable, $additional_variables ) ) {
                continue;
            }

            if ( ! in_array( $variable, $specials ) ) {

                if ( in_array( $variable, $sms_strings ) ) {
                    $message = str_replace( "%" . $variable . "%", $order->$variable, $message ); //Standard fields
                }
                elseif ( in_array( $variable, $wc_whatsapp_notify_variables ) ) {
                    $message = str_replace("%" . $variable . "%", $order_variables["_" . $variable][0], $message ); //Meta fields
                }
                elseif ( in_array( $variable, $custom_variables ) && isset( $order_variables[ $variable ] ) ) {
                    $message = str_replace( "%" . $variable . "%", $order_variables[ $variable][0] , $message );
                }
                elseif ( in_array( $variable, $additional_variables ) && isset( $additional_data[$variable] ) ) {
                    $message = str_replace("%" . $variable . "%", $additional_data[$variable], $message );
                }

            }
            elseif ( $variable === "order_date" || $variable === "modified_date" ) {
                $message = str_replace("%" . $variable . "%", date_i18n( woocommerce_date_format(), strtotime( $order->$variable ) ), $message );
            }
            elseif ( $variable === "shop_name" ) {
                $message = str_replace("%" . $variable . "%", get_bloginfo(' name' ), $message );
            }
            elseif ( $variable === "id" ) {
                $message = str_replace("%" . $variable . "%", $order->get_order_number(), $message );
            }
            elseif ( $variable === "order_product" ) {

                $products = $order->get_items();
                $quantity = $products[ key( $products ) ]['name'];

                if ( strlen( $quantity ) > 10 ) {
                    $quantity = substr( $quantity,  0, 10 ) . "...";
                }

                if ( count( $products ) > 1 ) {
                    $quantity .= " (+" . ( count( $products ) - 1 ) . ")";
                }

                $message = str_replace("%" . $variable . "%", $quantity, $message);
            }

            elseif ( $variable === "signature" ) {
                $message = str_replace("%" . $variable . "%", $this->wc_whatsapp_notify_field( 'signature' ), $message );
            }

        }
        return $message;
    }

    public function wc_whatsapp_notify_field( $var ) {
        global $wc_whatsapp_notify_settings;

        if ( $wc_whatsapp_notify_settings[ $var ] ) {
            return $wc_whatsapp_notify_settings[ $var ];
        }else{
            return ;
        }

    }

    public function process_phone( $order, $phone, $shipping = false, $owners_phone = false ) {

        //Sanitize phone number
        $phone = str_replace( array('+', '-' ), '', filter_var( $phone, FILTER_SANITIZE_NUMBER_INT ) );
        $phone = ltrim( $phone,  '0' );

        //Obtain country code prefix
        $country = WC()->countries->get_base_country();

        if ( ! $owners_phone ) {
            $country = $shipping ? $order->get_shipping_country() : $order->get_billing_country();
        }

        //get country name and prefix
        $intl_prefix = $this->country_prefix( $country );

        //Check for already Wc\WhatsAppluded prefix
        preg_match( "/(\d{1,4})[0-9.\- ]+/", $phone, $prefix );

        //If prefix hasn't been added already, add it
        if ( strpos( $prefix[1], $intl_prefix ) !== 0 ) {
            $phone = $intl_prefix . $phone;
        }

        return $phone;

    }

    public function country_prefix( $country = '' ) {

        $countries = array(
            'AC' => '247',
            'AD' => '376',
            'AE' => '971',
            'AF' => '93',
            'AG' => '1268',
            'AI' => '1264',
            'AL' => '355',
            'AM' => '374',
            'AO' => '244',
            'AQ' => '672',
            'AR' => '54',
            'AS' => '1684',
            'AT' => '43',
            'AU' => '61',
            'AW' => '297',
            'AX' => '358',
            'AZ' => '994',
            'BA' => '387',
            'BB' => '1246',
            'BD' => '880',
            'BE' => '32',
            'BF' => '226',
            'BG' => '359',
            'BH' => '973',
            'BI' => '257',
            'BJ' => '229',
            'BL' => '590',
            'BM' => '1441',
            'BN' => '673',
            'BO' => '591',
            'BQ' => '599',
            'BR' => '55',
            'BS' => '1242',
            'BT' => '975',
            'BW' => '267',
            'BY' => '375',
            'BZ' => '501',
            'CA' => '1',
            'CC' => '61',
            'CD' => '243',
            'CF' => '236',
            'CG' => '242',
            'CH' => '41',
            'CI' => '225',
            'CK' => '682',
            'CL' => '56',
            'CM' => '237',
            'CN' => '86',
            'CO' => '57',
            'CR' => '506',
            'CU' => '53',
            'CV' => '238',
            'CW' => '599',
            'CX' => '61',
            'CY' => '357',
            'CZ' => '420',
            'DE' => '49',
            'DJ' => '253',
            'DK' => '45',
            'DM' => '1767',
            'DO' => '1809',
            'DO' => '1829',
            'DO' => '1849',
            'DZ' => '213',
            'EC' => '593',
            'EE' => '372',
            'EG' => '20',
            'EH' => '212',
            'ER' => '291',
            'ES' => '34',
            'ET' => '251',
            'EU' => '388',
            'FI' => '358',
            'FJ' => '679',
            'FK' => '500',
            'FM' => '691',
            'FO' => '298',
            'FR' => '33',
            'GA' => '241',
            'GB' => '44',
            'GD' => '1473',
            'GE' => '995',
            'GF' => '594',
            'GG' => '44',
            'GH' => '233',
            'GI' => '350',
            'GL' => '299',
            'GM' => '220',
            'GN' => '224',
            'GP' => '590',
            'GQ' => '240',
            'GR' => '30',
            'GT' => '502',
            'GU' => '1671',
            'GW' => '245',
            'GY' => '592',
            'HK' => '852',
            'HN' => '504',
            'HR' => '385',
            'HT' => '509',
            'HU' => '36',
            'ID' => '62',
            'IE' => '353',
            'IL' => '972',
            'IM' => '44',
            'IN' => '91',
            'IO' => '246',
            'IQ' => '964',
            'IR' => '98',
            'IS' => '354',
            'IT' => '39',
            'JE' => '44',
            'JM' => '1876',
            'JO' => '962',
            'JP' => '81',
            'KE' => '254',
            'KG' => '996',
            'KH' => '855',
            'KI' => '686',
            'KM' => '269',
            'KN' => '1869',
            'KP' => '850',
            'KR' => '82',
            'KW' => '965',
            'KY' => '1345',
            'KZ' => '7',
            'LA' => '856',
            'LB' => '961',
            'LC' => '1758',
            'LI' => '423',
            'LK' => '94',
            'LR' => '231',
            'LS' => '266',
            'LT' => '370',
            'LU' => '352',
            'LV' => '371',
            'LY' => '218',
            'MA' => '212',
            'MC' => '377',
            'MD' => '373',
            'ME' => '382',
            'MF' => '590',
            'MG' => '261',
            'MH' => '692',
            'MK' => '389',
            'ML' => '223',
            'MM' => '95',
            'MN' => '976',
            'MO' => '853',
            'MP' => '1670',
            'MQ' => '596',
            'MR' => '222',
            'MS' => '1664',
            'MT' => '356',
            'MU' => '230',
            'MV' => '960',
            'MW' => '265',
            'MX' => '52',
            'MY' => '60',
            'MZ' => '258',
            'NA' => '264',
            'NC' => '687',
            'NE' => '227',
            'NF' => '672',
            'NG' => '234',
            'NI' => '505',
            'NL' => '31',
            'NO' => '47',
            'NP' => '977',
            'NR' => '674',
            'NU' => '683',
            'NZ' => '64',
            'OM' => '968',
            'PA' => '507',
            'PE' => '51',
            'PF' => '689',
            'PG' => '675',
            'PH' => '63',
            'PK' => '92',
            'PL' => '48',
            'PM' => '508',
            'PR' => '1787',
            'PR' => '1939',
            'PS' => '970',
            'PT' => '351',
            'PW' => '680',
            'PY' => '595',
            'QA' => '974',
            'QN' => '374',
            'QS' => '252',
            'QY' => '90',
            'RE' => '262',
            'RO' => '40',
            'RS' => '381',
            'RU' => '7',
            'RW' => '250',
            'SA' => '966',
            'SB' => '677',
            'SC' => '248',
            'SD' => '249',
            'SE' => '46',
            'SG' => '65',
            'SH' => '290',
            'SI' => '386',
            'SJ' => '47',
            'SK' => '421',
            'SL' => '232',
            'SM' => '378',
            'SN' => '221',
            'SO' => '252',
            'SR' => '597',
            'SS' => '211',
            'ST' => '239',
            'SV' => '503',
            'SX' => '1721',
            'SY' => '963',
            'SZ' => '268',
            'TA' => '290',
            'TC' => '1649',
            'TD' => '235',
            'TG' => '228',
            'TH' => '66',
            'TJ' => '992',
            'TK' => '690',
            'TL' => '670',
            'TM' => '993',
            'TN' => '216',
            'TO' => '676',
            'TR' => '90',
            'TT' => '1868',
            'TV' => '688',
            'TW' => '886',
            'TZ' => '255',
            'UA' => '380',
            'UG' => '256',
            'UK' => '44',
            'US' => '1',
            'UY' => '598',
            'UZ' => '998',
            'VA' => '379',
            'VA' => '39',
            'VC' => '1784',
            'VE' => '58',
            'VG' => '1284',
            'VI' => '1340',
            'VN' => '84',
            'VU' => '678',
            'WF' => '681',
            'WS' => '685',
            'XC' => '991',
            'XD' => '888',
            'XG' => '881',
            'XL' => '883',
            'XN' => '857',
            'XN' => '858',
            'XN' => '870',
            'XP' => '878',
            'XR' => '979',
            'XS' => '808',
            'XT' => '800',
            'XV' => '882',
            'YE' => '967',
            'YT' => '262',
            'ZA' => '27',
            'ZM' => '260',
            'ZW' => '263',
        );

        if ( $country === '' ) {
            return  $countries;
        } else {
            return isset( $countries[ $country ] ) ? $countries[ $country ] : '';
        }
    }
     
}

new wmnwSentMassages();