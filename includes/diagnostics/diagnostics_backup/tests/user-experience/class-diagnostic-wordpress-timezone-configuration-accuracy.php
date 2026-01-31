<?php
/**
 * WordPress Timezone Configuration Accuracy Diagnostic
 *
 * Ensures timezone is set correctly (not UTC) for scheduled tasks and dates.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WordPress Timezone Configuration Accuracy Class
 *
 * Tests timezone configuration.
 *
 * @since 1.26028.1905
 */
class Diagnostic_WordPress_Timezone_Configuration_Accuracy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wordpress-timezone-configuration-accuracy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WordPress Timezone Configuration Accuracy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Ensures timezone is set correctly (not UTC) for scheduled tasks and dates';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-experience';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$timezone_check = self::check_timezone_configuration();
		
		if ( ! $timezone_check['is_configured'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: current timezone */
					__( 'Timezone set to %s (scheduled posts/tasks may run at unexpected times)', 'wpshadow' ),
					$timezone_check['current_timezone']
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wordpress-timezone-configuration-accuracy',
				'meta'         => array(
					'current_timezone' => $timezone_check['current_timezone'],
					'is_utc'           => $timezone_check['is_utc'],
					'gmt_offset'       => $timezone_check['gmt_offset'],
				),
			);
		}

		return null;
	}

	/**
	 * Check timezone configuration.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_timezone_configuration() {
		$check = array(
			'is_configured'    => true,
			'current_timezone' => '',
			'is_utc'           => false,
			'gmt_offset'       => 0,
		);

		// Get timezone setting.
		$timezone_string = get_option( 'timezone_string' );
		$gmt_offset = get_option( 'gmt_offset' );

		if ( empty( $timezone_string ) ) {
			// Using UTC offset instead of named timezone.
			if ( 0 === (float) $gmt_offset ) {
				$check['current_timezone'] = 'UTC (default)';
				$check['is_utc'] = true;
				$check['is_configured'] = false;
			} else {
				$check['current_timezone'] = sprintf( 'UTC%+d', $gmt_offset );
				$check['gmt_offset'] = $gmt_offset;
			}
		} else {
			$check['current_timezone'] = $timezone_string;

			// Check if it's explicitly set to UTC.
			if ( 'UTC' === $timezone_string || 0 === strpos( $timezone_string, 'Etc/' ) ) {
				$check['is_utc'] = true;
				$check['is_configured'] = false;
			}
		}

		return $check;
	}
}
