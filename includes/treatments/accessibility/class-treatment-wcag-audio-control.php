<?php
/**
 * WCAG 1.4.2 Audio Control Treatment
 *
 * Validates that auto-playing audio can be paused or stopped.
 *
 * @since   1.6035.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCAG Auto-playing Audio Control Treatment Class
 *
 * Checks for auto-playing audio that interferes with screen readers (WCAG 1.4.2 Level A).
 *
 * @since 1.6035.1200
 */
class Treatment_WCAG_Audio_Control extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'wcag-audio-control';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Audio Control (WCAG 1.4.2)';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates that auto-playing audio can be paused, stopped, or controlled';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_WCAG_Audio_Control' );
	}
}
