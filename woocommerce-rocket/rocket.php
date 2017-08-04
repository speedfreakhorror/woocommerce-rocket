<?php
/*
Plugin Name: DBBL Rocket for WooCommerce
Plugin URI: https://wordpress.org/plugins/woocommerce-rocket/
Description: DBBL Rocket payment gateway integration for WooCommerce
Version: 1.0
Author: Ibrahim Jaber
Author URI: https://www.facebook.com/IbrahimjaberJR
License: GPL2
*/

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * WooCommerce - rocket integration
 *
 * @author Ibrahim Jaber @ UARTI.COM
 */
class uarti_rocket {

    private $db_version = '1.0';
    private $version_key = '_rocket_version';

    /**
     * Kick off the plugin
     */
    public function __construct() {
        add_action( 'plugins_loaded', array($this, 'init') );
        add_filter( 'woocommerce_payment_gateways', array($this, 'register_gateway') );

        register_activation_hook( __FILE__, array($this, 'install') );
    }

    /**
     * Load the plugin on `init` hook
     *
     * @return void
     */
    function init() {
        if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
            return;
        }

        require_once dirname( __FILE__ ) . '/includes/class-wc-rocket.php';
        require_once dirname( __FILE__ ) . '/includes/class-wc-gateway-rocket.php';
    }

    /**
     * Register WooCommerce Gateway
     *
     * @param  array  $gateways
     *
     * @return array
     */
    function register_gateway( $gateways ) {
        $gateways[] = 'WC_Gateway_rocket';

        return $gateways;
    }

    /**
     * Create the transaction table
     *
     * @return void
     */
    function install() {
        global $wpdb;

        $query = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wc_rocket` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `trxId` int(11) DEFAULT NULL,
            `sender` varchar(15) DEFAULT NULL,
            `ref` varchar(100) DEFAULT NULL,
            `amount` varchar(10) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `trxId` (`trxId`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        $wpdb->query( $query );

        $this->plugin_upgrades();

        update_option( $this->version_key, $this->db_version );
    }

    /**
     * Do plugin upgrade tasks
     *
     * @return void
     */
    private function plugin_upgrades() {
        global $wpdb;

        $version = get_option( $this->version_key, '0.1' );

        if ( version_compare( $this->db_version, $version, '<=' ) ) {
            return;
        }

        switch ( $version ) {
            case '0.1':
                $sql = "ALTER TABLE `{$wpdb->prefix}wc_rocket` CHANGE `trxId` `trxId` BIGINT(20) NULL DEFAULT NULL;";
                $wpdb->query( $sql );
                break;
        }
    }
}

new uarti_rocket();