<?php
/**
 * Mobile CLS (Cumulative Layout Shift) Treatment
 *
 * Calculates layout shifts during page load to prevent content jumps.
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
 * Mobile CLS (Cumulative Layout Shift) Treatment Class
 *
 * Calculates layout shifts during page load to prevent frustrating content jumps
 * that cause mis-taps, a Core Web Vitals metric for Google rankings.
 *
 * @since 1.6033.1645
 */
class Treatment_Mobile_CLS_Cumulative_Layout_Shift extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-cls-cumulative-layout-shift';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile CLS (Cumulative Layout Shift)';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Calculate layout shifts during page load (Core Web Vitals metric)';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_CLS_Cumulative_Layout_Shift' );
	}
}
