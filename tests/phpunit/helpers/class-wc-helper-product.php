<?php
/**
 * Product helpers.
 *
 * @package PreciseSales\Tests
 */

/**
 * Class WC_Helper_Product.
 *
 * This helper class should ONLY be used for unit tests!.
 */
class WC_Helper_Product {

	/**
	 * Delete a product.
	 *
	 * @param int $product_id ID to delete.
	 */
	public static function delete_product( $product_id ) {
		$product = wc_get_product( $product_id );
		if ( $product ) {
			$product->delete( true );
		}
	}

	/**
	 * Create simple product.
	 *
	 * @since 2.3
	 * @param bool  $save Save or return object.
	 * @param array $props Properties to be set in the new product, as an associative array.
	 * @return WC_Product_Simple
	 */
	public static function create_simple_product( $save = true, $props = [] ) {
		$product       = new WC_Product_Simple();
		$default_props =
			[
				'name'          => 'Dummy Product',
				'regular_price' => 10,
				'price'         => 10,
				'sku'           => 'DUMMY SKU',
				'manage_stock'  => false,
				'tax_status'    => 'taxable',
				'downloadable'  => false,
				'virtual'       => false,
				'stock_status'  => 'instock',
				'weight'        => '1.1',
			];

		$product->set_props( array_merge( $default_props, $props ) );

		if ( $save ) {
			$product->save();
			return wc_get_product( $product->get_id() );
		} else {
			return $product;
		}
	}
}
