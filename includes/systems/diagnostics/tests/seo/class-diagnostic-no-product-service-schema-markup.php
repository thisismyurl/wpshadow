<?php
/**
 * No Product/Service Schema Markup Diagnostic
 *
 * Detects when product/service schema is missing,
 * preventing rich product results in search.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\SEO
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Product/Service Schema Markup
 *
 * Checks whether product/service schema is implemented
 * for rich product search results.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Product_Service_Schema_Markup extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-product-service-schema-markup';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Product/Service Schema Markup';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether product schema is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if WooCommerce is active
		$has_woocommerce = is_plugin_active( 'woocommerce/woocommerce.php' );

		if ( ! $has_woocommerce ) {
			return null; // Not applicable
		}

		// Check homepage for product schema
		$homepage = wp_remote_get( home_url() );
		if ( is_wp_error( $homepage ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $homepage );

		// Check for Product schema
		$has_product_schema = preg_match( '/Product"|"@type":\s*"Product/i', $body );

		if ( ! $has_product_schema ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Product schema isn\'t implemented, which means missing rich product results. Product schema displays: price, availability, ratings in search results. This dramatically increases CTR (30-50% higher clicks). Schema includes: product name, image, price, currency, availability (in stock/out of stock), review ratings, brand. WooCommerce has built-in schema support, but often needs optimization. SEO plugins can enhance: Yoast, Rank Math, Schema Pro.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Ecommerce Search Visibility',
					'potential_gain' => '+30-50% CTR from rich product results',
					'roi_explanation' => 'Product schema displays prices and ratings in search, increasing click-through rates 30-50%.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/product-service-schema-markup?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
