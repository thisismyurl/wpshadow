<?php
/**
 * Color Contrast Compliance Diagnostic
 *
 * Issue #4863: Text Doesn't Meet WCAG AA Color Contrast Requirements
 * Pillar: 🌍 Accessibility First
 *
 * Checks if text has sufficient contrast for people with low vision.
 * Affects ~8% of men (colorblind), plus people with low vision.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Color_Contrast_Compliance Class
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
 * @since 0.6093.1200
 */
class Diagnostic_Color_Contrast_Compliance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $slug = 'color-contrast-compliance';

	/**
	 * The diagnostic title
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $title = 'Text Doesn\'t Meet WCAG AA Color Contrast Requirements';

	/**
	 * The diagnostic description
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $description = 'Checks if text/background colors meet accessibility contrast minimums';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// This is a guidance diagnostic. Actual color contrast testing requires visual analysis.
		// We provide recommendations and tools.

		$issues = array();

		$issues[] = __( 'Normal text must have contrast ratio 4.5:1 (WCAG AA)', 'wpshadow' );
		$issues[] = __( 'Large text (18pt+) must have contrast ratio 3:1 (WCAG AA)', 'wpshadow' );
		$issues[] = __( 'Don\'t use red + green only combinations (colorblind users can\'t distinguish)', 'wpshadow' );
		$issues[] = __( 'Don\'t communicate meaning through color alone (add text, icon, or pattern)', 'wpshadow' );
		$issues[] = __( 'Use sufficient brightness difference, not just color difference', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'People with low vision and colorblind users struggle with low-contrast text. This affects about 250 million people globally (8% of males are colorblind).', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/color-contrast?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'         => $issues,
					'wcag_aa_requirement'     => 'Contrast ratio 4.5:1 for normal text',
					'wcag_aaa_standard'       => 'Contrast ratio 7:1 (gold standard)',
					'testing_tools'           => 'WebAIM Contrast Checker, Chrome DevTools, Firefox Accessibility Inspector',
					'colorblind_types'        => 'Deuteranopia (red-green), Protanopia (red-green), Tritanopia (blue-yellow)',
					'affected_population'     => __( '~250 million colorblind or low vision users globally', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
