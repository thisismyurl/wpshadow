<?php
/**
 * Tap Target Spacing Validation Treatment
 *
 * Measures distance between adjacent interactive elements to prevent accidental activation.
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
 * Tap Target Spacing Validation Treatment Class
 *
 * Measures distance between adjacent interactive elements to prevent accidental
 * activation on touch devices, ensuring WCAG 2.5.8 compliance.
 *
 * @since 1.6033.1645
 */
class Treatment_Tap_Target_Spacing_Validation extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'tap-target-spacing-validation';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Tap Target Spacing Validation';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Measure distance between interactive elements to prevent accidental activation (WCAG 2.5.8)';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Tap_Target_Spacing_Validation' );
	}
}
