<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test: Active Plugins Count (Performance)
 *
 * Checks if too many plugins are active
 * Philosophy: Show value (#9) - fewer plugins = faster site
 *
 * @package WPShadow
 * @subpackage Diagnostics/Tests
 * @since 1.2601.2112
 */
class Test_Performance_ActivePluginsCount extends Diagnostic_Base {


	public static function check(): ?array {
		$plugins = get_plugins();
		$active  = get_option( 'active_plugins', array() );

		$active_count = count( $active );

		// More than 50 active plugins is excessive
		if ( $active_count > 50 ) {
			return array(
				'id'           => 'active-plugins-count',
				'title'        => sprintf( __( '%d plugins are active', 'wpshadow' ), $active_count ),
				'description'  => __( 'Too many plugins can slow down your site. Review and disable unnecessary plugins.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
			);
		}

		return null;
	}

	public static function test_live_active_plugins_count(): array {
		$result = self::check();

		if ( null === $result ) {
			return array(
				'passed'  => true,
				'message' => sprintf( __( 'Active plugins count is reasonable (%d)', 'wpshadow' ), count( get_option( 'active_plugins', array() ) ) ),
			);
		}

		return array(
			'passed'  => false,
			'message' => $result['description'],
		);
	}
}
