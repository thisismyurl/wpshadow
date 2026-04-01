<?php
/**
 * WCAG 2.4.7 Focus Visible Treatment
 *
 * Validates that keyboard focus indicators are visible.
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
 * WCAG Focus Visible Treatment Class
 *
 * Checks for visible keyboard focus indicators (WCAG 2.4.7 Level AA).
 *
 * @since 0.6093.1200
 */
class Treatment_WCAG_Focus_Visible extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'wcag-focus-visible';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Focus Visible (WCAG 2.4.7)';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates that keyboard focus indicators are visible';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_WCAG_Focus_Visible' );
	}
}
