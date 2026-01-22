<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Timezone extends Diagnostic_Base {

	protected static $slug = 'timezone';
	protected static $title = 'Timezone Configuration';
	protected static $description = 'Checks if timezone is properly configured with a named timezone instead of UTC offset.';

	public static function check(): ?array {
		$timezone_string = get_option( 'timezone_string' );
		$gmt_offset      = get_option( 'gmt_offset' );

		if ( empty( $timezone_string ) && ( empty( $gmt_offset ) || '0' === $gmt_offset ) ) {
			return null;
		}

		if ( empty( $timezone_string ) && ! empty( $gmt_offset ) && '0' !== $gmt_offset ) {
			return array(
				'finding_id'   => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Timezone is configured using UTC offset (%s) instead of a named timezone. This can cause issues with scheduled posts, backups, and cron tasks. Use a city-based timezone like "America/New_York" for proper DST handling.', 'wpshadow' ),
					$gmt_offset > 0 ? "+{$gmt_offset}" : $gmt_offset
				),
				'category'     => 'settings',
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'timestamp'    => current_time( 'mysql' ),
			);
		}

		return null;
	}
}
