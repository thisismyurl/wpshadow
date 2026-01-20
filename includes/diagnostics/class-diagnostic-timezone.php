<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Timezone extends Diagnostic_Base {

	protected function get_id(): string {
		return 'timezone';
	}

	protected function get_title(): string {
		return __( 'Timezone Configuration', 'wpshadow' );
	}

	protected function get_description(): string {
		return __( 'Checks if timezone is properly configured with a named timezone instead of UTC offset.', 'wpshadow' );
	}

	protected function get_category(): string {
		return 'settings';
	}

	protected function get_severity(): string {
		return 'low';
	}

	protected function is_auto_fixable(): bool {
		return false;
	}

	public function check(): ?array {
		$timezone_string = get_option( 'timezone_string' );
		$gmt_offset      = get_option( 'gmt_offset' );

		if ( empty( $timezone_string ) && ( empty( $gmt_offset ) || '0' === $gmt_offset ) ) {
			return null;
		}

		if ( empty( $timezone_string ) && ! empty( $gmt_offset ) && '0' !== $gmt_offset ) {
			return array(
				'finding_id'   => $this->get_id(),
				'title'        => $this->get_title(),
				'description'  => sprintf(
					__( 'Timezone is configured using UTC offset (%s) instead of a named timezone. This can cause issues with scheduled posts, backups, and cron tasks. Use a city-based timezone like "America/New_York" for proper DST handling.', 'wpshadow' ),
					$gmt_offset > 0 ? "+{$gmt_offset}" : $gmt_offset
				),
				'category'     => $this->get_category(),
				'severity'     => $this->get_severity(),
				'threat_level' => 25,
				'auto_fixable' => $this->is_auto_fixable(),
				'timestamp'    => current_time( 'mysql' ),
			);
		}

		return null;
	}
}
