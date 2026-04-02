<?php
/**
 * OPcache Enabled Treatment
 *
 * Verifies that PHP OPcache is enabled for optimal performance.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OPcache Enabled Treatment Class
 *
 * Checks if PHP OPcache is installed and enabled. OPcache dramatically
 * improves PHP performance by caching compiled bytecode.
 *
 * @since 1.6093.1200
 */
class Treatment_OPcache_Enabled extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'opcache-enabled';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'OPcache Enabled';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP OPcache is enabled';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Checks for OPcache availability and enabled status.
	 * OPcache can improve performance by 30-50%.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_OPcache_Enabled' );
	}
}
