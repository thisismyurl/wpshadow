<?php
/**
 * Homepage Loading Speed Treatment
 *
 * Checks homepage load speed, core performance metrics, and payload size.
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
 * Homepage Loading Speed Treatment Class
 *
 * Evaluates homepage speed metrics that impact user experience and conversion.
 *
 * @since 0.6093.1200
 */
class Treatment_Homepage_Loading_Speed extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'homepage-loading-speed';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Homepage Loading Speed Loses Customers';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks homepage speed, Core Web Vitals, and payload size';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance-optimization';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Homepage_Loading_Speed' );
	}
}
