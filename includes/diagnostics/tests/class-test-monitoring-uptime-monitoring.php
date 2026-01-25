<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test: Uptime Monitoring (Monitoring)
 *
 * Checks if uptime monitoring is configured
 * Philosophy: Show value (#9) - monitoring prevents downtime
 *
 * @package WPShadow
 * @subpackage Diagnostics/Tests
 * @since 1.2601.2112
 */
class Test_Monitoring_UptimeMonitoring extends Diagnostic_Base {


	public static function check(): ?array {
		// Check if uptime monitoring plugin is active
		$plugins           = get_plugins();
		$monitoring_active = false;

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			if (
				stripos( $plugin_file, 'uptime' ) !== false ||
				stripos( $plugin_file, 'healthcheck' ) !== false ||
				stripos( $plugin_file, 'monitor' ) !== false ||
				stripos( $plugin_file, 'jetpack' ) !== false
			) {
				if ( is_plugin_active( $plugin_file ) ) {
					$monitoring_active = true;
					break;
				}
			}
		}

		// WordPress.com sites have built-in monitoring
		$siteurl = get_option( 'siteurl' );
		if ( strpos( $siteurl, 'wordpress.com' ) !== false ) {
			$monitoring_active = true;
		}

		if ( ! $monitoring_active ) {
			return array(
				'id'           => 'uptime-monitoring',
				'title'        => __( 'Uptime monitoring not configured', 'wpshadow' ),
				'description'  => __( 'Enable uptime monitoring to get alerts if your site goes down. Use Jetpack, UpdraftPlus, or third-party service.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 40,
			);
		}

		return null;
	}

	public static function test_live_uptime_monitoring(): array {
		$result = self::check();

		if ( null === $result ) {
			return array(
				'passed'  => true,
				'message' => __( 'Uptime monitoring is configured', 'wpshadow' ),
			);
		}

		return array(
			'passed'  => false,
			'message' => $result['description'],
		);
	}
}
