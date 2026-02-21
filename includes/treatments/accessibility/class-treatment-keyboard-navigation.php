<?php
/**
 * Keyboard Navigation Treatment
 *
 * Checks for keyboard accessibility features including skip links,
 * focus indicators, and keyboard trap prevention.
 *
 * @package    WPShadow
 * @subpackage Treatments\Accessibility
 * @since      1.6035.1700
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Keyboard Navigation Treatment Class
 *
 * Verifies site is navigable via keyboard alone (no mouse required).
 * WCAG 2.1 Level A Success Criterion 2.1.1 (Keyboard).
 *
 * @since 1.6035.1700
 */
class Treatment_Keyboard_Navigation extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'keyboard_navigation';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Keyboard Navigation';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies site is fully navigable via keyboard';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1700
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Keyboard_Navigation' );
	}
}
