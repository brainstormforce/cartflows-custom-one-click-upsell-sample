<?php
/**
 * CartFlows Custom Gateway Integration Loader.
 *
 * @package cartflows-cgis
 */

if ( ! class_exists( 'Cartflows_Cgi_Loader' ) ) {

	/**
	 * Class Cartflows_Cgi_Loader.
	 */
	final class Cartflows_Cgi_Loader {

		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance = null;

		/**
		 *  Initiator
		 */
		public static function get_instance() {

			if ( is_null( self::$instance ) ) {

				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {

			// Activation hook.
			register_activation_hook( CARTFLOWS_CGIS_FILE, array( $this, 'activation_reset' ) );

			// deActivation hook.
			register_deactivation_hook( CARTFLOWS_CGIS_FILE, array( $this, 'deactivation_reset' ) );

			add_action( 'plugins_loaded', array( $this, 'load_plugin' ), 99 );

			add_action( 'wp_loaded', array( $this, 'add_gateway_integration_file') );
		}

		public function add_gateway_integration_file(){

			add_filter( 'cartflows_offer_supported_payment_gateways', array( $this, 'your_function_name' ) );
		}

		/**
		 * Loads plugin files.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function load_plugin() {

			define( 'CARTFLOWS_CGIS_DIR', plugin_dir_path( CARTFLOWS_CGIS_FILE ) );
			define( 'CARTFLOWS_CGIS_VER', '1.0.0' );
			define( 'CARTFLOWS_CGIS_PLUGIN_NAME', 'Custom Gateway integration Sample' );

			// Stop the loading of the plugin is required plugins are not found to avoid any further errors.
			add_action( 'admin_notices', array( $this, 'fails_to_load' ) );
		}

		/**
		 * Add new payment gateway in Supported Gateways.
		 *
		 * @param array $supported_gateways Supported Gateways by CartFlows.
		 * @return array.
		 */
		function your_function_name( $supported_gateways ){
			
			$supported_gateways['your_gateway_key'] = array(
				'file'  => 'your_gateway_key.php', // Your Custom code's file name
				'class' => 'Cartflows_Pro_Gateway_Your_Gateway',   // Class name used in the Custom Code's file.
				'path'  => CARTFLOWS_CGIS_DIR . 'gateway-files/class-cartflows-pro-gateway-tour-gateway.php', // Full File path where you have stored.
			);

			return $supported_gateways; // Adding the payment gateway name.
		}

		/**
		 * Activation Reset
		 */
		public function activation_reset() {

			
		}

		/**
		 * Deactivation Reset
		 */
		public function deactivation_reset() {
		}

		/**
		 * Fires admin notice when Elementor is not installed and activated.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function fails_to_load() {

			$screen          = get_current_screen();
			$screen_id       = $screen ? $screen->id : '';
			$allowed_screens = array(
				'toplevel_page_cartflows',
				'dashboard',
				'plugins',
			);

			if ( ! in_array( $screen_id, $allowed_screens, true ) ) {
				return;
			}

			$message 		='';
			$action_url 	='';
			$button_label 	='';

			$class = 'notice notice-warning';
			$plugin = 'cartflows-pro/cartflows-pro.php';
			$plugins = get_plugins();

			if( isset( $plugins[ $plugin ] ) ){

				// Check is the CartFlows PRO plugin is installed or not.
				if( ! is_plugin_active( $plugin ) ) {

					if ( ! current_user_can( 'activate_plugins' ) ) {
						return;
					}

					$message = sprintf( __( 'The %1$s plugin requires %2$s CartFlows PRO %3$s plugin to be activated.', 'cartflows' ), CARTFLOWS_CGIS_PLUGIN_NAME, '<strong>', '</strong>' );
					$action_url   = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );
					$button_label = __( 'Activate CartFlows PRO Now', 'cartflows' );
					
				}
			}else{
					
				if ( ! current_user_can( 'install_plugins' ) ) {
					return;
				}
				
				$message = sprintf( __( 'To use the %1$s plugin requires %2$s CartFlows PRO %3$s plugin to be installed & activated. Login to your account and download and install the CartFlows PRO on this website.', 'cartflows' ), CARTFLOWS_CGIS_PLUGIN_NAME, '<strong>', '</strong>' );
				$action_url   = 'https://my.cartflows.com/';
				$button_label = __( 'Download now!', 'cartflows' );
			}

			if( '' !== $message && '' !== $action_url ){
				$button = '<p><a href="' . $action_url . '" class="button-primary">' . $button_label . '</a></p><p></p>';
				printf( '<div class="%1$s"><p>%2$s</p>%3$s</div>', esc_attr( $class ), wp_kses_post( $message ), wp_kses_post( $button ) );
			}
		}

	}

	/**
	 *  Prepare if class 'Cartflows_Cgi_Loader' exist.
	 *  Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_Cgi_Loader::get_instance();
}

