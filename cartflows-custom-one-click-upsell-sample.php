<?php
/**
 * Plugin Name: Custom Gateway integration Sample
 * Plugin URI: https://cartflows.com/
 * Description: This plugin will give you more information about how to add the custom integration of your own payment gateway for the Upsell & Downsell.
 * Version: 1.0.0
 * Author: CartFlows Inc
 * Author URI: https://cartflows.com/
 * Text Domain: cartflows-cgis
 *
 * @package cartflows-cgis
 */

/**
 * Set constants.
 */
define( 'CARTFLOWS_CGIS_FILE', __FILE__ );

/**
 * Loader
 */
require_once 'classes/class-cartflows-cgis-loader.php';