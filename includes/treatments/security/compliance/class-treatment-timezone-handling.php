<?php
/**
 * Timezone Handling Treatment
 *
 * Issue #4921: Hardcoded UTC Times (No User Timezone)
 * Pillar: 🌐 Culturally Respectful
 *
 * Checks if times respect user timezone settings.
 * Displaying UTC to users is confusing.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Timezone_Handling Class
 *
 * @since 1.6050.0000
 */
class Treatment_Timezone_Handling extends Treatment_Base {

	protected static $slug = 'timezone-handling';
	protected static $title = 'Hardcoded UTC Times (No User Timezone)';
	protected static $description = 'Checks if times are displayed in user\'s timezone';
	protected static $family = 'compliance';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Use wp_date() instead of date() for timestamps', 'wpshadow' );
		$issues[] = __( 'Display times in user\'s timezone, not UTC', 'wpshadow' );
		$issues[] = __( 'Show timezone abbreviation: "2:00 PM EST"', 'wpshadow' );
		$issues[] = __( 'Use relative times: "2 hours ago"', 'wpshadow' );
		$issues[] = __( 'Respect WordPress timezone setting', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Showing "14:00 UTC" to a user in Tokyo is confusing. Display times in the user\'s timezone so they understand when things happen.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/timezone-handling',
				'details'      => array(
					'recommendations'         => $issues,
					'wp_functions'            => 'wp_date(), current_time(), human_time_diff()',
					'get_timezone'            => 'wp_timezone_string() or wp_timezone()',
					'display_format'          => 'User-friendly: "Today at 2:00 PM" or "2 hours ago"',
				),
			);
		}

		return null;
	}
}
