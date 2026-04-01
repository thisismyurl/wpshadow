<?php
/**
 * AVIF Image Format Support Treatment
 *
 * Checks if AVIF image format support is available for the most aggressive
 * compression with smallest file sizes.
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
 * AVIF Image Format Support Treatment Class
 *
 * Verifies AVIF support:
 * - Server support for AVIF encoding
 * - ImageMagick or libaom availability
 * - AVIF plugin detection
 * - File size savings estimate
 *
 * @since 0.6093.1200
 */
class Treatment_Avif_Image_Format_Support extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'avif-image-format-support';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'AVIF Image Format Support';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for AVIF image format support for maximum compression';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Avif_Image_Format_Support' );
	}
}
