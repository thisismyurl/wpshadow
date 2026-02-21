<?php
/**
 * Mobile Text Contrast Ratio Treatment
 *
 * Validates text contrast meets WCAG standards for mobile readability.
 *
 * @since   1.6033.1645
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Text Contrast Ratio Treatment Class
 *
 * Validates that text contrast meets WCAG standards, especially critical for mobile
 * devices used outdoors in sunlight, ensuring WCAG AA/AAA compliance.
 *
 * @since 1.6033.1645
 */
class Treatment_Mobile_Text_Contrast_Ratio extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-text-contrast-ratio';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Text Contrast Ratio';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validate text contrast meets WCAG standards for mobile readability (WCAG 1.4.3)';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Text_Contrast_Ratio' );
	}
}
