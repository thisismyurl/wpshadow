<?php
/**
 * WP-Cron Working Treatment
 *
 * Checks whether WP-Cron is enabled and scheduled events exist.
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
 * Treatment_WP_Cron_Working Class
 *
 * Validates WP-Cron configuration and scheduled events.
 *
 * @since 0.6093.1200
 */
class Treatment_WP_Cron_Working extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'wp-cron-working';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'WP-Cron Working';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether WP-Cron is enabled and scheduled';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'wordpress-health';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_WP_Cron_Working' );
	}
}