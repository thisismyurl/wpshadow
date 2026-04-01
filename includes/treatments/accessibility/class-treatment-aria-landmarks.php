<?php
/**
 * ARIA Landmarks Treatment
 *
 * Checks for proper ARIA landmark roles (navigation, main, banner, contentinfo)
 * which help screen reader users understand page structure and navigate quickly.
 *
 * @package    WPShadow
 * @subpackage Treatments\Accessibility
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ARIA Landmarks Treatment Class
 *
 * Verifies proper use of ARIA landmark roles for navigation.
 * WCAG 2.1 Level A Success Criterion1.0 (Info and Relationships).
 *
 * @since 0.6093.1200
 */
class Treatment_ARIA_Landmarks extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'aria_landmarks';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'ARIA Landmarks';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies proper ARIA landmark usage for screen readers';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_ARIA_Landmarks' );
	}
}
