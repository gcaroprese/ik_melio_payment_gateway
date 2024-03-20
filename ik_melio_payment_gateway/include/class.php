<?php
/*

Class Ik_Melio_PaymentGateway
Update: 29/08/2021
Author: Gabriel Caroprese

*/

add_action( 'plugins_loaded', 'ik_meliopg_gateway_init', 11 );

function ik_meliopg_gateway_init() {

	class Ik_Melio_PaymentGateway extends WC_Payment_Gateway {

		/**
		 * Constructor for the gateway.
		 */
		public function __construct() {
	  
			$this->id                 = 'ik_meliopg';
			$this->icon               = apply_filters('ik_meliopg_gateway_filter_icon', IK_MELIOPG_PLUGIN_DIR_PUBLIC.'\img\melio-icon.png' );
			$this->has_fields         = false;
			$this->method_title       = __( 'Melio Payments', 'ik_meliopg' );
			$this->method_description = __( 'Payments through Melio. Orders are marked as "on hold" upon receipt.','ik_meliopg' );
		  
			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();
		  
			// Define user set variables
			$this->title        = $this->get_option( 'title' );
			$this->description  = $this->get_option( 'description' );
			$this->instructions = $this->get_option( 'instructions', $this->description );
			$this->vendor = $this->get_option( 'vendor', '' );
		  
			// Actions
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		  
			// Customer Emails
			add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
		}
	
	
		/**
		 * Initialize Gateway Settings Form Fields
		 */
		public function init_form_fields() {
	  
			$this->form_fields = apply_filters( 'ik_meliopg_gateway_filter_fields', array(
		  
				'enabled' => array(
					'title'   => __( 'Enable/Disable', 'ik_meliopg' ), // enable/Disable
					'type'    => 'checkbox',
					'label'   => __( 'Enable payments with Melio', 'ik_meliopg' ),//enable Melio Payment
					'default' => 'yes'
				),
				
				'title' => array(
					'title'       => __( 'Titel', 'ik_meliopg' ),//Title
					'type'        => 'text',
					'description' => __( 'This controls the title for the payment method the customer will see during checkout.', 'ik_meliopg' ),
					'default'     => __( 'Melio Payments', 'ik_meliopg' ),
					'desc_tip'    => true,
				),
				
				'description' => array(
					'title'       => __( 'Description', 'ik_meliopg' ), //Description
					'type'        => 'textarea',
					'description' => __( 'You can pay using credit card, bank transfer or debit card.', 'ik_meliopg' ),
					'default'     => __( 'You can pay using credit card, bank transfer or debit card.', 'ik_meliopg' ),
					'desc_tip'    => true,
				),
				
				'instructions' => array(
					'title'       => __( 'Instructions', 'ik_meliopg' ),
					'type'        => 'textarea',
					'description' => __( 'Instructions that will be added to the thank you page and emails.', 'ik_meliopg' ),
					'default'     => '',
					'desc_tip'    => true,
				),
				'vendor' => array(
					'title'       => __( 'Vendor ID', 'ik_meliopg' ),
					'type'        => 'text',
					'description' => __( '', 'ik_meliopg' ),
					'default'     => '',
					'desc_tip'    => true,
				),
			) );
		}
	
	
		/**
		 * Output for the order received page.
		 */
		public function thankyou_page() {
			
			if (isset($_GET['key'])){
			    $key = sanitize_key($_GET['key']);
			    $order_id = absint(wc_get_order_id_by_order_key($key));
			    $order = new WC_Order($order_id);
			    if ($order){
			        $total = $order->get_total();
			    }
			}
            echo '<style>
            #ik_melio_payments_details{
                margin: 50px 0;
            }
            </style>
            <div id="ik_melio_payments_details">
            <h2>Pay With Melio</h2>';
            if ( $this->instructions ) {
				echo wpautop( wptexturize( $this->instructions ) );
			}
			if ( $this->vendor) {
			    echo '<p>Click on the button to make the payment.</p>
				<script src="//api.meliopayments.com/api/html/melio-button.js"></script>';
			    if (isset($total)){
			        echo '<div class="melio-button" data-vendor-link="'.$this->vendor.'"
data-invoice-number="'.$order_id.'" data-total-amount="'.$total.'"></div>';
                } else {
			        echo '<div class="melio-button" data-vendor-link="'.$this->vendor.'"></div>';
                }
			} else {
			    echo 'Contact the seller at '.get_option('admin_email').' in order to know how to make the payment.';
			}
			echo '</div>';
		}
	
	
		/**
		 * Add content to the WC emails.
		 *
		 * @access public
		 * @param WC_Order $order
		 * @param bool $sent_to_admin
		 * @param bool $plain_text
		 */
		public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		
			if ( $this->instructions && ! $sent_to_admin && $this->id === $order->payment_method && $order->has_status( 'on-hold' ) ) {
				echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
			}
		}
	
	
		/**
		 * Process the payment and return the result
		 *
		 * @param int $order_id
		 * @return array
		 */
		public function process_payment( $order_id ) {
	
			$order = wc_get_order( $order_id );
			
			// Mark as on-hold (we're awaiting the payment)
			$order->update_status( 'on-hold', __( 'Waiting for payment confirmation from Melio.', 'ik_meliopg' ) );
			
			// Reduce stock levels
			$order->reduce_order_stock();
			
			// Remove cart
			WC()->cart->empty_cart();
			
			// Return thankyou redirect
			return array(
				'result' 	=> 'success',
				'redirect'	=> $this->get_return_url( $order )
			);
		}
	
  }
}

?>