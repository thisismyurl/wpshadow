<?php
/**
 * Mobile Menu Existence Check Treatment
 *
 * Validates that a mobile-friendly navigation menu exists for screen widths <768px.
 *
 * @since 0.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Menu Existence Check Treatment Class
 *
 * Validates that a mobile-friendly navigation menu exists and is properly implemented
 * for screen widths <768px with keyboard and screen reader accessibility.
 *
 * @since 0.6093.1200
 */
class Treatment_Mobile_Menu_Existence_Check extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-menu-existence-check';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Menu Existence Check';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validate that a mobile-friendly navigation menu exists for screen widths <768px';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'navigation';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Menu_Existence_Check' );
	}
}
