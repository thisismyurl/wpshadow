<?php
/**
 * Mobile Responsiveness Treatment
 *
 * Issue #4872: Admin Interface Not Mobile Responsive
 * Pillar: 🌍 Accessibility First
 *
 * Checks if admin interface works on mobile devices.
 * ~60% of web traffic is mobile - admin should work on tablets too.
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
 * Treatment_Mobile_Responsiveness Class
 *
 * Checks for:
 * - Viewport meta tag set correctly
 * - Responsive layouts (not fixed widths)
 * - Touch-friendly buttons (44×44px minimum)
 * - Font sizes readable on mobile (16px+)
 * - No horizontal scrolling required
 * - Mobile-first CSS approach
 * - Touch targets have spacing (no accidental clicks)
 *
 * Why this matters:
 * - Site managers edit content from phones/tablets
 * - Emergency admin actions may happen on mobile
 * - ~60% of web traffic is mobile
 * - WordPress mobile admin app is basic - web admin should work too
 *
 * @since 0.6093.1200
 */
class Treatment_Mobile_Responsiveness extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $slug = 'mobile-responsiveness';

	/**
	 * The treatment title
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $title = 'Admin Interface Not Mobile Responsive';

	/**
	 * The treatment description
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $description = 'Checks if admin interface adapts to mobile/tablet devices';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Responsiveness' );
	}
}
