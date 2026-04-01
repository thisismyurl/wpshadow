<?php
/**
 * Mobile vs Desktop Speed Treatment
 *
 * Checks if mobile page speed is significantly worse than desktop.
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
 * Mobile vs Desktop Speed Treatment Class
 *
 * 58% of traffic is mobile. If mobile is 2x slower than desktop, you're giving
 * 58% of customers a bad experience.
 *
 * @since 0.6093.1200
 */
class Treatment_Mobile_Desktop_Speed_Gap extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-desktop-speed-gap';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Page Speed Significantly Worse Than Desktop';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if mobile speed is within 20% of desktop speed';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'mobile-optimization';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Desktop_Speed_Gap' );
	}
}
