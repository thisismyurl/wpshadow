<?php
/**
 * Plugin Update Impact Treatment
 *
 * Checks for outdated plugins and their potential impact on performance
 * and security, recommending updates.
 *
 * @since   1.6033.2083
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Update Impact Treatment Class
 *
 * Monitors plugin status:
 * - Outdated plugins count
 * - Performance plugins available
 * - Security plugin status
 * - Plugin dependency resolution
 *
 * @since 1.6033.2083
 */
class Treatment_Plugin_Update_Impact extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-update-impact';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Update Status';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for outdated plugins and update availability';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.2083
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Plugin_Update_Impact' );
	}
}
