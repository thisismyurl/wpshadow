<?php
/**
 * Mobile FID (First Input Delay) Treatment
 *
 * Measures time from first tap to browser response for interactivity.
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
 * Mobile FID (First Input Delay) Treatment Class
 *
 * Measures time from first tap to browser response, a Core Web Vitals metric
 * critical for user experience and Google rankings.
 *
 * @since 1.6093.1200
 */
class Treatment_Mobile_FID_First_Input_Delay extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-fid-first-input-delay';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile FID (First Input Delay)';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Measure time from first tap to browser response (Core Web Vitals metric)';

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
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_FID_First_Input_Delay' );
	}
}
