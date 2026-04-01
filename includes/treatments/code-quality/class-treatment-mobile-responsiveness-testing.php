<?php
/**
 * Mobile Responsiveness Testing Treatment
 *
 * Tests if site is properly responsive on mobile devices.
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
 * Mobile Responsiveness Testing Treatment Class
 *
 * Validates that the site is mobile-friendly with proper viewport
 * settings, touch targets, and responsive design.
 *
 * @since 0.6093.1200
 */
class Treatment_Mobile_Responsiveness_Testing extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-responsiveness-testing';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Responsiveness Testing';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if site is properly responsive on mobile devices';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * Tests mobile responsiveness including viewport meta tag,
	 * touch target sizes, and responsive images.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Responsiveness_Testing' );
	}
}
