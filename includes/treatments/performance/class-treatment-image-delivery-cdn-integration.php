<?php
/**
 * Image Delivery CDN Integration Treatment
 *
 * Verifies that images are being delivered through a CDN service for
 * optimal performance and global distribution.
 *
 * @since 1.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Delivery CDN Integration Treatment Class
 *
 * Verifies image CDN setup:
 * - CDN image URL detection
 * - Image optimization API
 * - CloudFlare or similar service
 * - Global distribution
 *
 * @since 1.6093.1200
 */
class Treatment_Image_Delivery_Cdn_Integration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-delivery-cdn-integration';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Image Delivery CDN Integration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for CDN image delivery optimization';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Image_Delivery_Cdn_Integration' );
	}
}
