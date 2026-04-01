<?php
/**
 * WCAG 2.2.1 Timing Adjustable Treatment
 *
 * Validates that time limits can be extended or disabled.
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
 * WCAG Timing Adjustable Treatment Class
 *
 * Checks for adjustable time limits (WCAG 2.2.1 Level A).
 *
 * @since 0.6093.1200
 */
class Treatment_WCAG_Timing_Adjustable extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'wcag-timing-adjustable';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Timing Adjustable (WCAG 2.2.1)';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates that time limits can be turned off, adjusted, or extended';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_WCAG_Timing_Adjustable' );
	}
}
