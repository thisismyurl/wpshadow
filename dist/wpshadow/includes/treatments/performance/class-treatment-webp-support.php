<?php
/**
 * WebP Support Treatment
 *
 * Checks if WebP image format support is enabled to reduce image file sizes.
 * WebP can reduce file sizes by 25-35% compared to JPEG/PNG.
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
 * WebP Support Treatment Class
 *
 * Verifies WebP image format support:
 * - Server support for WebP conversion
 * - ImageMagick or GD library availability
 * - WebP plugin installation and configuration
 * - Browser compatibility detection
 *
 * @since 1.6093.1200
 */
class Treatment_Webp_Support extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'webp-support';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'WebP Support';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if WebP image format is enabled for better compression';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Webp_Support' );
	}
}
