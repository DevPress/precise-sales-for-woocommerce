<?php
/**
 * Plugin Name: Precise Sales for WooCommerce
 * Plugin URI: https://devpress.com
 * Description: Allows to the minute scheduling for WooCommerce sales.
 * Version: 1.0.0
 * Author: DevPress
 * Author URI: https://devpress.com
 * Developer: Devin Price
 * Developer URI: https://devpress.com
 *
 * WC requires at least: 6.8.0
 * WC tested up to: 6.8.0
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */


/**
 * Class PreciseSales
 * @package PreciseSales
 */
class PreciseSales {

	/**
	 * The single instance of the class.
	 *
	 * @var mixed $instance
	 */
	protected static $instance;

	/**
	 * Main PreciseSales Instance.
	 *
	 * Ensures only one instance of the PreciseSales is loaded or can be loaded.
	 *
	 * @return PreciseSales - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
	}

}

PreciseSales::instance();
