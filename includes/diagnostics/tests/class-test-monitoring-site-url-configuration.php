<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test: Site URL Configuration (Monitoring)
 *
 * Checks if site URL is properly configured and matches
 * Philosophy: Show value (#9) - wrong URLs break functionality
 *
 * @package WPShadow
 * @subpackage Diagnostics/Tests
 * @since 1.2601.2112
 */
class Test_Monitoring_SiteUrlConfiguration extends Diagnostic_Base {


	public static function check(): ?array {
		$siteurl = get_option( 'siteurl' );
		$home    = get_option( 'home' );

		// Check if URLs are empty
		if ( empty( $siteurl ) || empty( $home ) ) {
			return array(
				'id'           => 'site-url-configuration',
				'title'        => __( 'Site URL configuration is missing', 'wpshadow' ),
				'description'  => __( 'Configure Site URL and Home URL (Settings > General) properly to avoid redirect issues.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 95,
			);
		}

		// Check if they match
		if ( $siteurl !== $home ) {
			// This can be valid for some setups, so it's low priority
			return null;
		}

		// Check if using HTTP on production (should use HTTPS)
		if ( strpos( $siteurl, 'http://' ) === 0 && strpos( $siteurl, 'localhost' ) === false && strpos( $siteurl, '127.0.0.1' ) === false ) {
			return array(
				'id'           => 'site-url-configuration',
				'title'        => __( 'Site URL is using HTTP instead of HTTPS', 'wpshadow' ),
				'description'  => __( 'Change Site URL to HTTPS (Settings > General) for better security.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
			);
		}

		return null;
	}

	public static function test_live_site_url_configuration(): array {
		$result = self::check();

		if ( null === $result ) {
			return array(
				'passed'  => true,
				'message' => __( 'Site URL configuration is correct', 'wpshadow' ),
			);
		}

		return array(
			'passed'  => false,
			'message' => $result['description'],
		);
	}
}
