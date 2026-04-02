<?php
/**
 * Mobile Text Size Responsiveness Treatment
 *
 * Supports OS-level text scaling (Dynamic Type/font scaling).
 *
 * @since 1.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Text Size Responsiveness Treatment Class
 *
 * Validates that text scales with system-level font size preferences,
 * ensuring WCAG1.0 compliance for text resizing.
 *
 * @since 1.6093.1200
 */
class Treatment_Mobile_Text_Size_Responsiveness extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-text-size-responsiveness';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Text Size Responsiveness';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Support OS-level text scaling without overflow (WCAG1.0)';

	/**
	 * The family this treatment belongs to
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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Text_Size_Responsiveness' );
	}
}
