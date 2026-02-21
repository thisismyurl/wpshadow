<?php
/**
 * Treatment: Hover-Dependent Functionality Detection
 *
 * Detects CSS :hover states and JavaScript hover events without touch equivalents,
 * making features inaccessible on mobile devices.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4028
 *
 * @package    WPShadow\Treatments\Mobile
 * @since      1.6034.1440
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hover-Dependent Functionality Treatment
 *
 * Detects hover-only interactions that don't work on touch devices.
 * All hover interactions should have touch equivalents (tap, long-press).
 *
 * @since 1.6034.1440
 */
class Treatment_Mobile_Hover_Dependent extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-hover-dependent';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Hover-Dependent Functionality Detection';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects hover-only interactions inaccessible on touch devices';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Check for hover-dependent functionality.
	 *
	 * Analyzes theme CSS and JavaScript for hover-only interactions.
	 * Common issues: dropdown menus, tooltips, hidden content.
	 *
	 * @since  1.6034.1440
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Hover_Dependent' );
	}
}
