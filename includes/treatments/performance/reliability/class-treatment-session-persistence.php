<?php
/**
 * Session Persistence Treatment
 *
 * Checks whether user sessions survive server restarts.
 *
 * @package    WPShadow
 * @subpackage Treatments\Reliability
 * @since      1.6035.1400
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Session Persistence Treatment Class
 *
 * Verifies that sessions are stored in a persistent location.
 *
 * @since 1.6035.1400
 */
class Treatment_Session_Persistence extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'session-persistence';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Sessions Lost on Server Restart';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if sessions are stored persistently';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'reliability';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Session_Persistence' );
	}
}
