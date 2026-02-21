<?php
/**
 * OPcache Configuration Treatment
 *
 * Checks OPcache settings are optimized for WordPress performance.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.2050
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OPcache Configuration Treatment Class
 *
 * Verifies OPcache configuration is optimized for WordPress.
 * Checks memory, file limits, and revalidation settings.
 *
 * @since 1.6033.2050
 */
class Treatment_OPcache_Configuration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'opcache-configuration';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'OPcache Configuration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks OPcache configuration for WordPress optimization';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Checks OPcache configuration against WordPress best practices.
	 *
	 * @since  1.6033.2050
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_OPcache_Configuration' );
	}
}
