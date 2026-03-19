<?php
/**
 * Color Contrast Compliance Treatment
 *
 * Issue #4863: Text Doesn't Meet WCAG AA Color Contrast Requirements
 * Pillar: 🌍 Accessibility First
 *
 * Checks if text has sufficient contrast for people with low vision.
 * Affects ~8% of men (colorblind), plus people with low vision.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Color_Contrast_Compliance Class
 *
 * Checks for:
 * - WCAG AA minimum contrast ratio 4.5:1 for normal text
 * - WCAG AA minimum contrast ratio 3:1 for large text (18pt+)
 * - WCAG AAA minimum contrast ratio 7:1 for normal text (gold standard)
 * - Colorblind-safe color palettes (avoid red-green only combinations)
 * - Not relying on color alone to communicate meaning
 *
 * Why this matters:
 * - Low contrast is hard for anyone to read (airport security, sunny day)
 * - Colorblindness affects 8% of males, 0.5% of females
 * - Red-green colorblindness most common, blue-yellow also exists
 * - Age-related vision loss also reduces color perception
 *
 * @since 1.6093.1200
 */
class Treatment_Color_Contrast_Compliance extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'color-contrast-compliance';

	/**
	 * The treatment title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'Text Doesn\'t Meet WCAG AA Color Contrast Requirements';

	/**
	 * The treatment description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Checks if text/background colors meet accessibility contrast minimums';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Color_Contrast_Compliance' );
	}
}
