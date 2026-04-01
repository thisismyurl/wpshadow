<?php
/**
 * Color Contrast Accessibility Treatment
 *
 * Tests if text has sufficient color contrast for readability.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Color Contrast Accessibility Treatment Class
 *
 * Validates that text and UI elements meet WCAG 2.1 AA color contrast
 * requirements for users with visual impairments.
 *
 * @since 0.6093.1200
 */
class Treatment_Color_Contrast_Accessibility extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'color-contrast-accessibility';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Color Contrast Accessibility';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if text has sufficient color contrast for readability';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * Tests color contrast ratios for text, links, buttons, and
	 * other UI elements against WCAG standards.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Color_Contrast_Accessibility' );
	}
}
