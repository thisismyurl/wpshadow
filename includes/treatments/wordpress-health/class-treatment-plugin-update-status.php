<?php
/**
 * Plugin Update Status Treatment
 *
 * Checks for outdated plugins that require updates.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1435
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Plugin_Update_Status Class
 *
 * Detects pending plugin updates.
 *
 * @since 1.6035.1435
 */
class Treatment_Plugin_Update_Status extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-update-status';

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
	protected static $description = 'Checks for plugins that require updates';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'wordpress-health';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1435
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Plugin_Update_Status' );
	}
}