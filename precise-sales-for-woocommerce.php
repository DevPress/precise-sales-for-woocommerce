<?php
/**
 * Plugin Name: Precise Sales for WooCommerce
 * Plugin URI: https://devpress.com
 * Description: Allows WooCommerce sales to be scheduled to the minute.
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
		add_filter( 'gettext', [ $this, 'change_sale_schedule_tooltip' ], 20, 3 );
		add_action( 'woocommerce_product_options_pricing', [ $this, 'add_product_sale_time' ] );
		add_action( 'woocommerce_admin_process_product_object', [ $this, 'save_product_sale_time' ] );
	}

	/**
	 * Changes the tooltip of sale schedule.
	 *
	 * @param string $translation
	 * @param string $text
	 * @param string $domain
	 *
	 * @return string
	 */
	public function change_sale_schedule_tooltip( $translation, $text, $domain ) {
		if ( 'woocommerce' !== $domain ) {
			return $translation;
		}

		if ( $translation !== $text ) {
			return $translation;
		}

		if ( 'The sale will start at 00:00:00 of "From" date and end at 23:59:59 of "To" date.' !== $text ) {
			return $translation;
		}

		return 'The sale will start at specified time of "From" date and end at the specific time of "To" date.';
	}

	/**
	 * Saves the product sale time.
	 *
	 * @param \WC_Product $product
	 */
	public function save_product_sale_time( $product ) {
		$date_on_sale_from = '';
		$date_on_sale_to   = '';

		// Force date from to beginning of day.
		if ( isset( $_POST['_sale_price_dates_from'] ) ) {
			$date_on_sale_from = wc_clean( wp_unslash( $_POST['_sale_price_dates_from'] ) );

			$from_time     = isset( $_POST['_sale_price_time_from'] ) && $_POST['_sale_price_time_from'] ? wc_clean( wp_unslash( $_POST['_sale_price_time_from'] ) ) : '00:00:00';
			$from_time_arr = explode( ':', $from_time );
			if ( count( $from_time_arr ) < 3 ) {
				// We miss seconds.
				$from_time .= ':00';
			}

			if ( ! empty( $date_on_sale_from ) ) {
				$date_on_sale_from  = date( 'Y-m-d', strtotime( $date_on_sale_from ) );
				$date_on_sale_from .= ' ' . $from_time;
			}
		}

		// Forces date to the end of the day.
		if ( isset( $_POST['_sale_price_dates_to'] ) ) {
			$date_on_sale_to = wc_clean( wp_unslash( $_POST['_sale_price_dates_to'] ) );

			$to_time     = isset( $_POST['_sale_price_time_to'] ) && $_POST['_sale_price_time_to'] ? wc_clean( wp_unslash( $_POST['_sale_price_time_to'] ) ) : '00:00:00';
			$to_time_arr = explode( ':', $to_time );
			if ( count( $to_time_arr ) < 3 ) {
				// We miss seconds.
				$to_time .= ':59';
			}

			if ( ! empty( $date_on_sale_to ) ) {
				$date_on_sale_to  = date( 'Y-m-d', strtotime( $date_on_sale_to ) );
				$date_on_sale_to .= ' ' . $to_time;
			}
		}

		$errors = $product->set_props(
			array(
				'date_on_sale_from' => $date_on_sale_from,
				'date_on_sale_to'   => $date_on_sale_to,
			)
		);

		if ( is_wp_error( $errors ) ) {
			\WC_Admin_Meta_Boxes::add_error( $errors->get_error_message() );
		}
	}

	/**
	 * Script for simple products/subscriptions.
	 */
	public function script() {
		?>
		<script>
			(function($){
				$.fn.uyInputFilter = function(inputFilter) {
					return this.on("input keydown keyup mousedown mouseup select contextmenu drop", function() {
						var values = this.value.split(':'),
							value1 = values[0],
							value2 = values[1] || '';

						if ( value1.length > 2 ) {
							value2 = value1.substr(2);
							value1 = value1.substr(0, 2);
						}

						if ( value2.length > 2 ) {
							value2 = value2.substr(0, 2);
						}

						if (inputFilter(value1)) {
							this.oldValue = value1;
							this.oldSelectionStart = this.selectionStart;
							this.oldSelectionEnd = this.selectionEnd;
							if ( value1 < 0 ) {
								value1 = 0;
							}

							if ( value1 > 23 ) {
								value1 = 23;
							}
						} else if (this.hasOwnProperty("oldValue")) {
							value1 = this.oldValue;
							this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
						} else {
							value1 = "";
						}

						if (value2) {
							if (inputFilter(value2)) {
								this.oldValue2 = value2;
								this.oldSelectionStart = this.selectionStart;
								this.oldSelectionEnd = this.selectionEnd;
								if ( value2 < 0 ) {
									value2 = 0;
								}
								if ( value2 > 59 ) {
									value2 = 59;
								}
							} else if (this.hasOwnProperty("oldValue2")) {
								value2 = this.oldValue2;
								this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
							} else {
								value2 = "";
							}
						}

						var value = value1;

						if ( value2 ) {
							value += ':';
							value += value2;
						}

						this.value = value;
					});
				};

				function psHookTimeInputs() {
					$("._sale_price_time_from:not(.uy-filtered), ._sale_price_time_to:not(.uy-filtered)").each(function(){
						$(this).uyInputFilter(function(value) {
							return /^\d*$/.test(value);    // Allow digits only, using a RegExp
						}).addClass('uy-filtered');
					})
				}

				$(function(){
					psHookTimeInputs();

					var timeFromInput = $('#_sale_price_time_from');

					if ( timeFromInput.length ) {
						timeFromInput.insertAfter('#_sale_price_dates_from');
						timeFromInput.css('margin-bottom','1em');
						timeFromInput.datepicker("destroy");
					}

					var timeToInput = $('#_sale_price_time_to');

					if ( timeToInput.length ) {
						timeToInput.insertAfter('#_sale_price_dates_to');
						timeToInput.css('margin-bottom','1em');
						$('#_sale_price_dates_to').css('margin-bottom', '1em');
						timeToInput.datepicker("destroy");
					}
				});
			})(jQuery);
		</script>
		<style>
			@media screen and (min-width:1281px) {
				.sale_price_dates_fields:not(.variation-time-fields) ._sale_price_time_from,
				.sale_price_dates_fields:not(.variation-time-fields) ._sale_price_time_to {
					width: 100px !important;
					margin-left: 10px;
				}
				.sale_price_dates_fields:not(.variation-time-fields) ._sale_price_time_from {
					clear: none !important;
				}

				.sale_price_dates_fields:not(.variation-time-fields) ._sale_price_dates_to {
					clear: left;
				}

				.sale_price_dates_fields:not(.variation-time-fields) ._sale_price_time_to {
					float: left;
				}
			}
		</style>
		<?php
	}

	/**
	 * Get Product Time.
	 *
	 * @param \WC_Product $product
	 * @param string      $type
	 * @param string      $format
	 */
	public function get_product_time( $product, $type = 'from', $format = 'H:i' ) {
		if ( 'from' === $type ) {
			$timestamp = $product->get_date_on_sale_from( 'edit' ) ? $product->get_date_on_sale_from( 'edit' )->getOffsetTimestamp() : false;
		} else {
			$timestamp = $product->get_date_on_sale_to( 'edit' ) ? $product->get_date_on_sale_to( 'edit' )->getOffsetTimestamp() : false;
		}

		if ( ! $timestamp ) {
			return '';
		}

		return date_i18n( $format, $timestamp );
	}

	/**
	 * Add Sale Time.
	 */
	public function add_product_sale_time() {
		global $product_object;

		$sale_price_time_from = $this->get_product_time( $product_object );
		$sale_price_time_to   = $this->get_product_time( $product_object, 'to' );
		?>
		<div class="hide-if-js">
			<p class="form-field sale_price_dates_fields">
				<label for="_sale_price_time_from"><?php esc_html_e( 'Sale Time From', 'universalyums' ); ?></label>
				<input pattern="[0-9]{2}:[0-9]{2}"  type="text" class="short _sale_price_time_from" name="_sale_price_time_from" id="_sale_price_time_from" value="<?php echo esc_attr( $sale_price_time_from ); ?>" placeholder="HH:mm" />
			</p>
			<p class="form-field sale_price_dates_fields">
				<label for="_sale_price_time_to"><?php esc_html_e( 'Sale Time To', 'universalyums' ); ?></label>
				<input pattern="[0-9]{2}:[0-9]{2}"  type="text" class="short _sale_price_time_to" name="_sale_price_time_to" id="_sale_price_time_to" value="<?php echo esc_attr( $sale_price_time_to ); ?>" placeholder="HH:mm" />
			</p>
		</div>
		<?php
		add_action( 'admin_footer', [ $this, 'script' ] );
	}

}

PreciseSales::instance();
