<?php
/**
 * Plugin Name: Precise Sales for WooCommerce
 * Plugin URI: https://devpress.com/products/precise-sales-for-woocommerce/
 * Description: Allows WooCommerce sales to be scheduled to the minute.
 * Version: 1.0.0
 * Author: DevPress
 * Author URI: https://devpress.com
 *
 * WC requires at least: 5.9.1
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
	 * Saves the product sale time with hours and minutes and minutes.
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
				// We don't save to the second.
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
				// We don't save to the second.
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
	 * Get the product sale time with default format returning "H:i".
	 *
	 * @param \WC_Product $product
	 * @param string      $type
	 * @param string      $format
	 */
	public function get_product_sale_time( $product, $type = 'from', $format = 'H:i' ) {
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
	 * Display new sale time fields on the prodict page.
	 */
	public function add_product_sale_time() {
		global $product_object;

		$sale_price_time_from = $this->get_product_sale_time( $product_object );
		$sale_price_time_to   = $this->get_product_sale_time( $product_object, 'to' );
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

	/**
	 * Script for simple products and subscriptions.
	 */
	public function script() {
		?>
		<script>
			(function($){
				$.fn.psInputFilter = function(inputFilter) {
					return this.on("input keydown keyup mousedown mouseup select contextmenu drop", function() {
						const values = this.value.split(':');
						let [hour, minute = ''] = values;

						if ( hour.length > 2 ) {
							minute = hour.substr(2);
							hour = hour.substr(0, 2);
						}

						if ( minute.length > 2 ) {
							minute = minute.substr(0, 2);
						}

						if (inputFilter(hour)) {
							this.oldValue = hour;
							this.oldSelectionStart = this.selectionStart;
							this.oldSelectionEnd = this.selectionEnd;
							if ( hour < 0 ) {
								hour = 0;
							}

							if ( hour > 23 ) {
								hour = 23;
							}
						} else if (this.hasOwnProperty("oldValue")) {
							hour = this.oldValue;
							this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
						} else {
							hour = "";
						}

						if (minute) {
							if (inputFilter(minute)) {
								this.oldminute = minute;
								this.oldSelectionStart = this.selectionStart;
								this.oldSelectionEnd = this.selectionEnd;
								if ( minute < 0 ) {
									minute = 0;
								}
								if ( minute > 59 ) {
									minute = 59;
								}
							} else if (this.hasOwnProperty("oldminute")) {
								minute = this.oldminute;
								this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
							} else {
								minute = "";
							}
						}

						let value = hour;

						if ( minute ) {
							value += ':';
							value += minute;
						}

						this.value = value;
					});
				};

				function psHookTimeInputs() {
					$("._sale_price_time_from:not(.ps-filtered), ._sale_price_time_to:not(.ps-filtered)").each(function(){
						$(this).psInputFilter(function(value) {
							return /^\d*$/.test(value);    // Allow digits only, using a RegExp
						}).addClass('ps-filtered');
					})
				}

				$(function(){
					psHookTimeInputs();

					const timeFromInput = $('#_sale_price_time_from');

					if ( timeFromInput.length ) {
						timeFromInput.insertAfter('#_sale_price_dates_from');
						timeFromInput.css('margin-bottom','1em');
						timeFromInput.datepicker("destroy");
					}

					const timeToInput = $('#_sale_price_time_to');

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

}

PreciseSales::instance();
