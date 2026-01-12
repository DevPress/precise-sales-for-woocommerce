<?php
/**
 * Tests for the PreciseSales class.
 *
 * @package PreciseSales\Tests
 */

namespace PreciseSales\Test\Unit;

use WP_UnitTestCase;
use WC_Helper_Product;

/**
 * Test case for PreciseSales functionality.
 */
class Precise_Sales_Test extends WP_UnitTestCase {

	/**
	 * Test that sale times are correctly retrieved from a product.
	 */
	public function test_sales_time() {
		$product = WC_Helper_Product::create_simple_product();
		$product->set_props(
			[
				'date_on_sale_from' => '2022-12-01 12:12:00',
				'date_on_sale_to'   => '2022-12-20 11:11:00',
			]
		);

		$product->save();

		$sale = new \PreciseSales();

		$this->assertEquals( '12:12', $sale->get_product_sale_time( $product ) );
		$this->assertEquals( '11:11', $sale->get_product_sale_time( $product, 'to' ) );
	}

	/**
	 * Test that a product is marked as on sale when within scheduled time.
	 */
	public function test_sale() {
		$product = WC_Helper_Product::create_simple_product();
		$time    = current_time( 'timestamp' );
		$product->set_props(
			[
				'date_on_sale_from' => date( 'Y-m-d H:i:s', $time ),
				'date_on_sale_to'   => date( 'Y-m-d 23:59:59', $time ),
				'sale_price'        => 20,
				'regular_price'     => 40,
				'price'             => 40,
			]
		);

		$product->save();

		// Let's simulate WC Sales CRON.
		wc_scheduled_sales();

		$this->assertTrue( $product->is_on_sale() );
	}

	/**
	 * Test that sale price is applied during scheduled sale period.
	 */
	public function test_sale_price() {
		$product = WC_Helper_Product::create_simple_product();
		$time    = current_time( 'timestamp' );
		$product->set_props(
			[
				'date_on_sale_from' => date( 'Y-m-d 00:00:00', $time ),
				'date_on_sale_to'   => date( 'Y-m-d 23:59:59', $time ),
				'sale_price'        => 20,
				'regular_price'     => 40,
				'price'             => 40,
			]
		);

		$product->save();
		$price = $product->get_price();

		$this->assertEquals( 20, $price );
	}

	/**
	 * Test that regular price is used when sale is scheduled in the future.
	 */
	public function test_no_sale_price() {
		$product = WC_Helper_Product::create_simple_product();
		$time    = current_time( 'timestamp' ) + MONTH_IN_SECONDS;
		$product->set_props(
			[
				'date_on_sale_from' => date( 'Y-m-d 00:00:00', $time ),
				'date_on_sale_to'   => date( 'Y-m-d 23:59:59', $time ),
				'sale_price'        => 20,
				'regular_price'     => 40,
				'price'             => 40,
			]
		);

		$product->save();
		$price = $product->get_price();

		$this->assertEquals( 40, $price );
	}

	/**
	 * Test midnight sale time (00:00).
	 */
	public function test_midnight_sale_time() {
		$product = WC_Helper_Product::create_simple_product();
		$product->set_props(
			[
				'date_on_sale_from' => '2024-01-15 00:00:00',
				'date_on_sale_to'   => '2024-01-20 00:00:00',
			]
		);

		$product->save();

		$sale = new \PreciseSales();

		$this->assertEquals( '00:00', $sale->get_product_sale_time( $product ) );
		$this->assertEquals( '00:00', $sale->get_product_sale_time( $product, 'to' ) );
	}

	/**
	 * Test end of day sale time (23:59).
	 */
	public function test_end_of_day_sale_time() {
		$product = WC_Helper_Product::create_simple_product();
		$product->set_props(
			[
				'date_on_sale_from' => '2024-01-15 23:59:00',
				'date_on_sale_to'   => '2024-01-20 23:59:00',
			]
		);

		$product->save();

		$sale = new \PreciseSales();

		$this->assertEquals( '23:59', $sale->get_product_sale_time( $product ) );
		$this->assertEquals( '23:59', $sale->get_product_sale_time( $product, 'to' ) );
	}

	/**
	 * Test empty time returns empty string.
	 */
	public function test_empty_time_returns_empty_string() {
		$product = WC_Helper_Product::create_simple_product();
		// Don't set any sale dates.
		$product->save();

		$sale = new \PreciseSales();

		$this->assertEquals( '', $sale->get_product_sale_time( $product ) );
		$this->assertEquals( '', $sale->get_product_sale_time( $product, 'to' ) );
	}

	/**
	 * Test tooltip modification via gettext filter.
	 */
	public function test_tooltip_modification() {
		$sale = new \PreciseSales();

		// Test that non-WooCommerce domain is not modified.
		$result = $sale->change_sale_schedule_tooltip( 'test', 'test', 'other-plugin' );
		$this->assertEquals( 'test', $result );

		// Test that different text is not modified.
		$result = $sale->change_sale_schedule_tooltip( 'Some other text', 'Some other text', 'woocommerce' );
		$this->assertEquals( 'Some other text', $result );

		// Test that the correct tooltip is modified.
		$original_text = 'The sale will start at 00:00:00 of "From" date and end at 23:59:59 of "To" date.';
		$result        = $sale->change_sale_schedule_tooltip( $original_text, $original_text, 'woocommerce' );
		$this->assertEquals( 'The sale will start at specified time of "From" date and end at the specific time of "To" date.', $result );

		// Test that already translated text is not modified.
		$result = $sale->change_sale_schedule_tooltip( 'Translated text', $original_text, 'woocommerce' );
		$this->assertEquals( 'Translated text', $result );
	}

}
