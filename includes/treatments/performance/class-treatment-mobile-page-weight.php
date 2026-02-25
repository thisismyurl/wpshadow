<?php
/**
 * Mobile Page Weight Detection
 *
 * Calculates total page size served to mobile users to identify excessive bandwidth consumption.
 *
 * @package    WPShadow
 * @subpackage Treatments\Performance
 * @since      1.602.1430
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Page Weight Detection
 *
 * Measures total page weight (HTML + CSS + JS + images) served to mobile users.
 * High page weights consume data plans, slow down load times, and impact Core Web Vitals.
 *
 * @since 1.602.1430
 */
class Treatment_Mobile_Page_Weight extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-page-weight-excessive';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Page Weight Detection';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Detects when mobile pages exceed recommended size limits';

	/**
	 * The treatment family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Measures total page weight in:
	 * - Initial load (<1MB recommended)
	 * - Total page (<3MB recommended)
	 * - Above-fold resources (<500KB recommended)
	 *
	 * @since  1.602.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Page_Weight' );
	}
}
