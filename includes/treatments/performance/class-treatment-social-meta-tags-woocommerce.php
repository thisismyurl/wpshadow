<?php
/**
 * Social Meta Tags for WooCommerce Products
 *
 * Validates social meta tag implementation for e-commerce product pages.
 *
 * @since 0.6093.1200
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
 * @since 0.6093.1200
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
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Social_Meta_Tags_WooCommerce' );
	}
}
