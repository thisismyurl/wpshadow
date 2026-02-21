<?php
/**
 * Mobile Page Weight Detection Treatment
 *
 * Calculates total page size served to mobile users to identify excessive bandwidth.
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
 * Mobile Page Weight Detection Treatment Class
 *
 * Calculates total page size (HTML + CSS + JS + images) served to mobile users
 * to identify excessive bandwidth consumption affecting search rankings and UX.
 *
 * @since 1.6033.1645
 */
class Treatment_Mobile_Page_Weight_Detection extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-page-weight-detection';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Page Weight Detection';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Calculate total page size served to mobile users to identify excessive bandwidth';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Page_Weight_Detection' );
	}
}
