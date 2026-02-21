<?php
/**
 * Mobile Performance Focus Treatment
 *
 * Tests if mobile performance is prioritized through responsive design,
 * mobile-specific optimizations, and mobile user experience features.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1400
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Performance Focus Treatment Class
 *
 * Evaluates whether the site is optimized for mobile devices
 * including responsive design, mobile speed, and mobile UX.
 *
 * @since 1.6035.1400
 */
class Treatment_Mobile_Performance_Focus extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'optimizes-mobile-performance';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Performance Focus';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if mobile performance is prioritized';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the mobile performance focus treatment check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if mobile performance issues detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Performance_Focus' );
	}
}
