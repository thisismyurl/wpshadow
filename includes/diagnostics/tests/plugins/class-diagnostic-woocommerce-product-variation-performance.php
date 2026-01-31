<?php
/**
 * WooCommerce Product Variation Performance Diagnostic
 *
 * Checks if product variations are affecting performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Product Variation Performance Diagnostic Class
 *
 * Detects product variation performance issues.
 *
 * @since 1.2601.2310
 */
class Diagnostic_WooCommerce_Product_Variation_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'woocommerce-product-variation-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WooCommerce Product Variation Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for variation performance issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if WooCommerce is active
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}

		global $wpdb;

		// Count products with excessive variations
		$products_with_many_variations = $wpdb->get_var(
			"SELECT COUNT(DISTINCT post_parent) FROM {$wpdb->posts} 
			WHERE post_type = 'product_variation' 
			GROUP BY post_parent 
			HAVING COUNT(*) > 100"
		);

		if ( $products_with_many_variations ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( '%d products have excessive variations (100+). This can slow down product pages and loading times.', 'wpshadow' ),
					absint( $products_with_many_variations )
				),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/woocommerce-product-variation-performance',
			);
		}

		return null;
	}
}
