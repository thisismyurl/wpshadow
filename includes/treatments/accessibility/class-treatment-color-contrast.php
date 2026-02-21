<?php
/**
 * Color Contrast Treatment
 *
 * Checks theme for WCAG AA color contrast compliance (4.5:1 for normal text,
 * 3:1 for large text) to ensure readability for visually impaired users.
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
 * Color Contrast Treatment Class
 *
 * Verifies theme has sufficient color contrast for readability.
 * WCAG 2.1 Level AA Success Criterion 1.4.3 (Contrast Minimum).
 *
 * @since 1.6035.1700
 */
class Treatment_Color_Contrast extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'color_contrast';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Color Contrast';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies theme has WCAG AA color contrast (4.5:1 minimum)';

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
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Color_Contrast' );
	}
}
