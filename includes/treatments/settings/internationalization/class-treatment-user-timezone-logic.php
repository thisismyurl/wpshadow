<?php
/**
 * User Timezone Logic Treatment
 *
 * Checks whether time-based features use user timezones.
 *
 * @package    WPShadow
 * @subpackage Treatments\Internationalization
 * @since      1.6035.1400
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
 * @since 1.6035.1400
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
	 * @since  1.6035.1400
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
				'kb_link'      => 'https://wpshadow.com/kb/user-timezone-logic',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
