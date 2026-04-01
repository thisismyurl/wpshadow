<?php
/**
 * Theme Performance and Optimization
 *
 * Validates theme performance and optimization.
 *
 * @since 0.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Theme_Performance Class
 *
 * Checks theme performance and optimization.
 *
 * @since 0.6093.1200
 */
class Treatment_Theme_Performance extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-performance';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Performance';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates theme performance and optimization';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'theme-quality';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Theme_Performance' );
	}
}
