<?php
/**
 * WooCommerce Product Structured Data Not Optimized Diagnostic
 *
 * Checks if WooCommerce product structured data is optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2347
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Product Structured Data Not Optimized Diagnostic Class
 *
 * Detects unoptimized WooCommerce structured data.
 *
 * @since 1.2601.2347
 */
class Diagnostic_WooCommerce_Product_Structured_Data_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'woocommerce-product-structured-data-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WooCommerce Product Structured Data Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if WooCommerce structured data is optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2347
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return null;
		}

		// Check if WooCommerce structured data filter exists
		if ( ! has_filter( 'woocommerce_structured_data_product' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'WooCommerce product structured data is not optimized. Add schema markup to improve product visibility in search results.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 25,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/woocommerce-product-structured-data-not-optimized',
			);
		}

		return null;
	}
}
