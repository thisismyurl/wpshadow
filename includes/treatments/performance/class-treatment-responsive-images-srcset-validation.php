<?php
/**
 * Responsive Images Srcset Validation Treatment
 *
 * Verifies that images use srcset attribute with multiple resolutions to ensure
 * optimal image delivery across different screen sizes and devices.
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
 * Responsive Images Srcset Validation Treatment Class
 *
 * Analyzes responsive image implementation:
 * - Srcset attribute usage
 * - Multiple image resolutions
 * - Sizes attribute presence
 * - Picture element usage
 *
 * @since 0.6093.1200
 */
class Treatment_Responsive_Images_Srcset_Validation extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'responsive-images-srcset-validation';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Responsive Images Srcset Validation';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies responsive image srcset for optimal device delivery';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Responsive_Images_Srcset_Validation' );
	}
}
