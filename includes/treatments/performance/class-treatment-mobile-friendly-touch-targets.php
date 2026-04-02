<?php
/**
 * Mobile-Friendly Touch Targets Treatment
 *
 * Checks if interactive elements (buttons, links) are sized appropriately for
 * touch input on mobile devices.
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
 * Mobile-Friendly Touch Targets Treatment Class
 *
 * Verifies touch-friendly design:
 * - Touch target size (48x48px recommended)
 * - Spacing between elements
 * - Mobile-optimized buttons
 * - Tap-friendly interactions
 *
 * @since 1.6093.1200
 */
class Treatment_Mobile_Friendly_Touch_Targets extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-friendly-touch-targets';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile-Friendly Touch Targets';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if interactive elements are sized for touch input';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Friendly_Touch_Targets' );
	}
}
