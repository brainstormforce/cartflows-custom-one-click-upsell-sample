<?php
/**
 * Your Payment Gateway.
 *
 * @package cartflows-cgis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Pro_Gateway_Your_Gateway.
 */
class Cartflows_Pro_Gateway_Your_Gateway {

	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Key name variable
	 *
	 * @var key
	 */
	public $key = 'your_gateway_key';

	/**
	 * Refund supported variable
	 *
	 * @var is_api_refund
	 */
	public $is_api_refund = true;


	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {

		add_action( 'cartflows_offer_subscription_created', array( $this, 'add_subscription_payment_meta' ), 10, 3 );
	}

	/**
	 * Get WooCommerce payment getaways.
	 *
	 * @return object
	 */
	public function get_wc_gateway() {

		global $woocommerce;

		$gateways = $woocommerce->payment_gateways->payment_gateways();

		return $gateways[ $this->key ];
	}

	/**
	 * After payment process.
	 *
	 * @param array $order order data.
	 * @param array $product product data.
	 * @return array
	 */
	public function process_offer_payment( $order, $product ) {

		$is_successful = false;

		try {

			$gateway = $this->get_wc_gateway();

			$order_source = $gateway->prepare_order_source( $order );

			$response = "pass this generate payment request function to your gateway's request API function to create a charge"; //$this->generate_payment_request( $order, $order_source, $product )

			if ( ! is_wp_error( $response ) ) {

				if ( ! empty( $response->error ) ) {

					wcf()->logger->log( '==== Error Response start ==== \n' . print_r( $response, true ) . '==== Error Response end ====\n' ); //phpcs:ignore

					$is_successful = false;
				} else {

					/** '_transaction_id', $response->id */
					$is_successful = true;

					$this->store_offer_transaction( $order, $response, $product );
				}
			}

			// @todo Show actual error if any.
		} catch ( Exception $e ) { //phpcs:ignore

			// @todo Exception catch to show actual error.
		}

		return $is_successful;
	}

	/**
	 * Generate payment request.
	 *
	 * @param object  $order order data.
	 * @param object  $order_source order source.
	 * @param array  $product product data.
	 * @return array
	 */
	protected function generate_payment_request( $order, $order_source, $product ) {

		$gateway               = $this->get_wc_gateway();
		$post_data             = array();
		$post_data['currency'] = strtolower( $order ? $order->get_currency() : get_woocommerce_currency() );
		$post_data['amount']   = 'your total amount';
		/* translators: %1s site name */
		$post_data['description'] = sprintf( __( '%1$s - Order %2$s - One Time offer', 'cartflows-cgis' ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $order->get_order_number() );

		/* translators: %1s order number */
		$post_data['statement_descriptor'] = sprintf( __( 'Order %1$s-OTO', 'cartflows-cgis' ), $order->get_order_number() );

		$post_data['capture'] = $gateway->capture ? 'true' : 'false';
		$billing_first_name   = $order->get_billing_first_name();
		$billing_last_name    = $order->get_billing_last_name();
		$billing_email        = $order->get_billing_email();

		if ( ! empty( $billing_email ) ) {
			$post_data['receipt_email'] = $billing_email;
		}

		$metadata = array(
			__( 'customer_name', 'cartflows-cgis' )  => sanitize_text_field( $billing_first_name ) . ' ' . sanitize_text_field( $billing_last_name ),
			__( 'customer_email', 'cartflows-cgis' ) => sanitize_email( $billing_email ),
			'order_id'                              => $order->get_order_number() . '_' . $product['id'] . '_' . $product['step_id'],
		);

		$post_data['expand[]'] = 'balance_transaction';
		$post_data['metadata'] = 'order Meta data'; //$metadata

		if ( $order_source->customer ) {
			$post_data['customer'] = $order_source->customer;
		}

		return apply_filters( 'your_prefix_generate_payment_request', $post_data, $order, $order_source );
	}
	/**
	 * Store Offer Trxn Charge.
	 *
	 * @param WC_Order $order    The order that is being paid for.
	 * @param Object   $response The response that is send from the payment gateway.
	 * @param array    $product  The product data.
	 */
	public function store_offer_transaction( $order, $response, $product ) {

		$order->update_meta_data( 'cartflows_offer_txn_resp_' . $product['step_id'], $response->id );
		$order->update_meta_data( '_cartflows_offer_txn_source_id_' . $product['step_id'], $response->payment_method );
		$order->update_meta_data( '_cartflows_offer_txn_customer_id_' . $product['step_id'], $response->customer );
		$order->save();
	}

	/**
	 * Process offer refund
	 *
	 * @param object $order Order Object.
	 * @param array  $offer_data offer data.
	 *
	 * @return string/bool.
	 */
	public function process_offer_refund( $order, $offer_data ) {

		$transaction_id = $offer_data['transaction_id'];
		$refund_amount  = $offer_data['refund_amount'];

		$order_currency = $order->get_currency( $order );

		$request     = array();
		$response_id = false;

		if ( ! is_null( $refund_amount ) ) {

			$request['amount'] = 'your_refund_amount';
			$request['charge'] = $transaction_id;
			$response          = 'your Refund API call should be here passing the $request args';

			if ( ! empty( $response->error ) || ! $response ) {
				$response_id = false;
			} else {
				/**
				 * Update sripe transaction amounts
				 */
				$this->get_wc_gateway()->update_fees( $order, $response->balance_transaction );

				$response_id = isset( $response->id ) ? $response->id : true;
			}
		}

		return $response_id;
	}

	/**
	 * Allow gateways to declare whether they support offer refund
	 *
	 * @return bool
	 */
	public function is_api_refund() {

		return $this->is_api_refund;
	}

	/**
	 * Setup the Payment data for Automatic Subscription.
	 *
	 * @param WC_Subscription $subscription An instance of a subscription object.
	 * @param object          $order Object of order.
	 * @param array           $offer_product array of offer product.
	 */
	public function add_subscription_payment_meta( $subscription, $order, $offer_product ) {

		if ( $this->$key === $order->get_payment_method() ) {

			$subscription_id = $subscription->get_id();

			update_post_meta( $subscription_id, 'your_transaction_source_id_key', $order->get_meta( 'your_transaction_source_id_key', true ) );
			update_post_meta( $subscription_id, 'your_transaction_customer_id_key', $order->get_meta( 'your_transaction_customer_id_key', true ) );
		}
	}

}

/**
 *  Prepare if class 'Cartflows_Pro_Gateway_Your_Gateway' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Gateway_Your_Gateway::get_instance();
