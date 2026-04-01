<?php
/**
 * Next-Gen Image Format Conversion Treatment
 *
 * Checks if images are being converted to next-generation formats (AVIF, WebP)
 * to maximize compression and file size reduction.
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
 * Next-Gen Image Format Conversion Treatment Class
 *
 * Verifies next-gen image conversion:
 * - AVIF conversion availability
 * - WebP conversion status
 * - Format plugin detection
 * - Automatic conversion
 *
 * @since 0.6093.1200
 */
class Treatment_Next_Gen_Image_Format_Conversion extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'next-gen-image-format-conversion';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Next-Gen Image Format Conversion';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for automatic conversion to AVIF/WebP formats';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Next_Gen_Image_Format_Conversion' );
	}
}
