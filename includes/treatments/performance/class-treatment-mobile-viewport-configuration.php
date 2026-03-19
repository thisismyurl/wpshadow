<?php
/**
 * Mobile Viewport Configuration Treatment
 *
 * Checks if mobile viewport is properly configured for responsive design
 * and proper display on mobile devices.
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
 * Mobile Viewport Configuration Treatment Class
 *
 * Verifies responsive design setup:
 * - Viewport meta tag
 * - Responsive viewport settings
 * - Mobile-friendly design
 * - Touch optimization
 *
 * @since 1.6093.1200
 */
class Treatment_Mobile_Viewport_Configuration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-viewport-configuration';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Viewport Configuration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for proper mobile viewport configuration for responsive design';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Viewport_Configuration' );
	}
}
