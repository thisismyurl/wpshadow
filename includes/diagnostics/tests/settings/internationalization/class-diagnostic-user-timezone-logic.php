<?php
/**
 * User Timezone Logic Diagnostic
 *
 * Checks whether time-based features use user timezones.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Internationalization
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Timezone Logic Diagnostic Class
 *
 * Verifies that timezone settings are configured.
 *
 * @since 0.6093.1200
 */
class Diagnostic_User_Timezone_Logic extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'user-timezone-logic';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Time-Based Logic Uses Server Time Not User Time';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether timezone settings are configured for users';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'internationalization';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$timezone_string = (string) get_option( 'timezone_string', '' );
		$gmt_offset      = (float) get_option( 'gmt_offset', 0 );

		$stats['timezone_string'] = $timezone_string ? $timezone_string : 'not set';
		$stats['gmt_offset']      = $gmt_offset;

		if ( empty( $timezone_string ) && 0 === $gmt_offset ) {
			$issues[] = __( 'No site timezone configured, which can confuse global visitors', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Clear timezones help customers understand deadlines and schedules. Using local time or showing the timezone avoids confusion.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/user-timezone-logic?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
