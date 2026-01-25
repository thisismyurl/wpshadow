<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Security_HSTS_Missing extends Diagnostic_Base {


	protected static $slug        = 'test-security-hsts-missing';
	protected static $title       = 'HSTS Presence Test';
	protected static $description = 'Tests for missing Strict-Transport-Security header.';

	public static function check( ?string $url = null, ?string $html = null ): ?array {
		$check_url = $url ?? home_url( '/' );

		if ( strpos( $check_url, 'https://' ) !== 0 ) {
			return null;
		}

		$response = wp_remote_get(
			$check_url,
			array(
				'timeout'   => 10,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$headers  = wp_remote_retrieve_headers( $response );
		$has_hsts = isset( $headers['strict-transport-security'] );

		if ( ! $has_hsts ) {
			return array(
				'id'            => 'security-no-hsts',
				'title'         => 'No HSTS Header',
				'description'   => 'HTTPS site without Strict-Transport-Security header. HSTS prevents protocol downgrade attacks and cookie hijacking.'
				'kb_link' => 'https://wpshadow.com/kb/hsts-header/',
				'training_link' => 'https://wpshadow.com/training/https-hardening/',
				'auto_fixable'  => false,
				'threat_level'  => 45,
				'module'        => 'Security',
				'priority'      => 2,
				'meta'          => array( 'has_hsts' => false ),
			);
		}

		return null;
	}

	public static function get_name(): string {
		return __( 'HSTS Presence', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks that Strict-Transport-Security header is set.', 'wpshadow' );
	}
}
