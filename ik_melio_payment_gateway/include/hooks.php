<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/* 
Ik Melio PG Hooks
Last Update: 29/08/2021
Author: Gabriel Caroprese
*/

// if plugin WooCommerce is not installed a message will show up and the plugin will deactivate itself
add_action( 'admin_notices', 'ik_meliopg_plugin_dependencies' );
function ik_meliopg_plugin_dependencies() {
    if (!class_exists('WC_Order')) {
    echo '<div class="error"><p>' . __( 'Warning! The plugin  <a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a>, is needed in order to make IK Melio Payment Gateway work.' ) . '</p></div>';
        $pluginURL = 'ik_melio_payment_gateway/ik_melio_payment_gateway.php';
        deactivate_plugins($pluginURL);
    }
}

// Add the gateway to WC Available Gateways
function ik_meliopg_add_to_gateways( $gateways ) {
	$gateways[] = 'Ik_Melio_PaymentGateway';
	return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'ik_meliopg_add_to_gateways' );


//Add edit links for payment gateway
function ik_meliopg_plugin_links( $links ) {

	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=ik_meliopg' ) . '">' . __( 'Set Up', 'ik_meliopg' ) . '</a>'
	);

	return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . IK_MELIOPG_PLUGIN_DIR, 'ik_meliopg_plugin_links' );
?>