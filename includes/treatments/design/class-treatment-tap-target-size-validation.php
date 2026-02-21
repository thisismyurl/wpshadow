<?php
/**
 * Tap Target Size Validation Treatment
 *
 * Measures interactive element dimensions to ensure they're large enough for accurate tapping.
 *
 * @since   1.6033.1645
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tap Target Size Validation Treatment Class
 *
 * Measures interactive element dimensions to ensure they're large enough for accurate
 * tapping on mobile devices, ensuring WCAG 2.5.5 compliance.
 *
 * @since 1.6033.1645
 */
class Treatment_Tap_Target_Size_Validation extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'tap-target-size-validation';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Tap Target Size Validation';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Measure interactive element dimensions to ensure sufficient size for accurate tapping (WCAG 2.5.5)';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'usability';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Tap_Target_Size_Validation' );
	}
}
