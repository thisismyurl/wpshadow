<?php
/**
 * Tap Target Size Validation
 *
 * Validates that interactive elements are at least 44×44px for accurate tapping.
 *
 * @package    WPShadow
 * @subpackage Treatments\Accessibility
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tap Target Size Validation
 *
 * Checks that buttons, links, and form controls are at least 44×44px (Apple HIG)
 * or 48×48px (Material Design) to support accurate tapping on mobile devices.
 * WCAG 2.5.5 Level AAA requirement.
 *
 * @since 1.6093.1200
 */
class Treatment_Tap_Target_Size extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'tap-targets-too-small';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Tap Target Size Validation';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Ensures mobile tap targets meet minimum 44×44px size';

	/**
	 * The treatment family.
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Tap_Target_Size' );
	}
}
