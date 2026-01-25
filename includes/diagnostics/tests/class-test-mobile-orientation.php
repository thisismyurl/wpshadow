<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Mobile_Orientation extends Diagnostic_Base {


	protected static $slug        = 'test-mobile-orientation';
	protected static $title       = 'Mobile Orientation Support Test';
	protected static $description = 'Tests for orientation lock restrictions';

	public static function check( ?string $url = null, ?string $html = null ): ?array {
		if ( $html !== null ) {
			return self::analyze_html( $html, $url ?? 'provided-html' );
		}

		$html = self::fetch_html( $url ?? home_url( '/' ) );
		if ( $html === false ) {
			return null;
		}

		return self::analyze_html( $html, $url ?? home_url( '/' ) );
	}

	protected static function analyze_html( string $html, string $checked_url ): ?array {
		// Check for orientation lock in media queries or scripts
		$has_orientation_lock = preg_match( '/screen\.orientation\.lock|orientation:\s*portrait|orientation:\s*landscape/i', $html );

		// Check for orientation-specific CSS that might cause issues
		preg_match_all( '/@media[^{]*(orientation:\s*(portrait|landscape))/i', $html, $orientation_media );
		$orientation_query_count = count( $orientation_media[0] );

		// Orientation-specific media queries are GOOD, but orientation LOCK is BAD
		// We're checking for JavaScript orientation lock here
		if ( $has_orientation_lock && preg_match( '/screen\.orientation\.lock/i', $html ) ) {
			return array(
				'id'            => 'mobile-orientation-locked',
				'title'         => 'Orientation Lock Detected',
				'description'   => 'JavaScript orientation lock detected. WCAG SC 1.3.4 requires content to support both portrait and landscape unless a specific orientation is essential.'
				'kb_link' => 'https://wpshadow.com/kb/orientation-support/',
				'training_link' => 'https://wpshadow.com/training/mobile-accessibility/',
				'auto_fixable'  => false,
				'threat_level'  => 55,
				'module'        => 'Accessibility',
				'priority'      => 2,
				'meta'          => array(
					'has_lock'            => true,
					'orientation_queries' => $orientation_query_count,
					'checked_url'         => $checked_url,
				),
			);
		}

		return null; // PASS - no orientation lock
	}

	protected static function fetch_html( string $url ) {
		$response = wp_remote_get(
			$url,
			array(
				'timeout'   => 10,
				'sslverify' => false,
			)
		);
		return is_wp_error( $response ) ? false : wp_remote_retrieve_body( $response );
	}

	public static function get_name(): string {
		return __( 'Mobile Orientation Support', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks that orientation is not locked (WCAG 1.3.4).', 'wpshadow' );
	}
}
