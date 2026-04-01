<?php
/**
 * Poor Color Contrast Treatment
 *
 * Detects insufficient color contrast ratios between text and backgrounds
 * that fail WCAG 2.1 Level AA requirements (4.5:1 for normal text, 3:1 for large text).
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
 * Poor Color Contrast Treatment Class
 *
 * Analyzes theme colors and common color combinations to detect contrast
 * ratio failures that make content difficult or impossible to read for
 * users with low vision or color blindness.
 *
 * **Why This Matters:**
 * - WCAG 2.1 Level AA compliance (SC1.0 Contrast Minimum)
 * - Legal requirement for ADA/Section 508 compliance
 * - 8% of men have color blindness
 * - Poor contrast = 53% user abandonment
 *
 * **WCAG Standards:**
 * - Normal text: 4.5:1 minimum contrast ratio
 * - Large text (18pt+): 3:1 minimum contrast ratio
 * - UI components: 3:1 minimum contrast ratio
 *
 * @since 0.6093.1200
 */
class Treatment_Poor_Color_Contrast extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'poor-color-contrast';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Poor Color Contrast Detected';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies insufficient color contrast ratios that fail WCAG 2.1 Level AA accessibility standards';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check
	 *
	 * Analyzes theme colors and common design elements for contrast issues:
	 * - Text color vs background color
	 * - Link colors vs background
	 * - Button colors and hover states
	 * - Custom CSS color declarations
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Poor_Color_Contrast' );
	}
}
