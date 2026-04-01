<?php
/**
 * Performance Culture Treatment
 *
 * Tests if team treats performance as a priority through
 * monitoring, documentation, and optimization practices.
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
 * Performance Culture Treatment Class
 *
 * Evaluates whether the site demonstrates organizational
 * commitment to performance optimization.
 *
 * @since 0.6093.1200
 */
class Treatment_Performance_Culture extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'builds-performance-culture';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Performance Culture';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if team treats performance as priority';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the performance culture treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if performance culture issues detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Performance_Culture' );
	}
}
