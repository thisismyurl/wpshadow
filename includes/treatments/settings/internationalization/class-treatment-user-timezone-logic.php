<?php
/**
 * User Timezone Logic Treatment
 *
 * Checks whether time-based features use user timezones.
 *
 * @package    WPShadow
 * @subpackage Treatments\Internationalization
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Timezone Logic Treatment Class
 *
 * Verifies that timezone settings are configured.
 *
 * @since 0.6093.1200
 */
class Treatment_User_Timezone_Logic extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'user-timezone-logic';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Time-Based Logic Uses Server Time Not User Time';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether timezone settings are configured for users';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'internationalization';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_User_Timezone_Logic' );
	}
}
