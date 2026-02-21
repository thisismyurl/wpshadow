<?php
/**
 * Social Meta Tags for WooCommerce Products
 *
 * Validates social meta tag implementation for e-commerce product pages.
 *
 * @since   1.6030.2148
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Social_Meta_Tags_WooCommerce Class
 *
 * Checks for proper social meta tag implementation on WooCommerce product pages.
 *
 * @since 1.6030.2148
 */
class Treatment_Social_Meta_Tags_WooCommerce extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'social-meta-tags-woocommerce';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Social Meta Tags for WooCommerce Products';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates social meta tag setup for product pages';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'social-media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Social_Meta_Tags_WooCommerce' );
	}

	/**
	 * Check if SEO plugin with WooCommerce support is installed.
	 *
	 * @since  1.6030.2148
	 * @return bool True if SEO plugin active.
	 */
	private static function has_seo_plugin() {
		$seo_plugins = array(
			'wordpress-seo-premium/wp-seo-premium.php',
			'wordpress-seo/wp-seo.php',
			'seo-by-rank-math-pro/rank-math.php',
			'seo-by-rank-math/rank-math.php',
			'all-in-one-seo-pack-pro/all_in_one_seo_pack.php',
			'all-in-one-seo-pack/all_in_one_seo_pack.php',
			'the-seo-framework/the-seo-framework.php',
			'seopress-pro/seopress.php',
			'seopress/seopress.php',
		);

		foreach ( $seo_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}
}
