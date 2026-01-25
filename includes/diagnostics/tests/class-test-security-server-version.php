<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Security_Server_Version extends Diagnostic_Base {


	protected static $slug        = 'test-security-server-version';
	protected static $title       = 'Server Version Exposure Test';
	protected static $description = 'Tests for server header leaking version information.';

	public static function check( ?string $url = null, ?string $html = null ): ?array {
		$check_url = $url ?? home_url( '/' );
		$response  = wp_remote_get(
			$check_url,
			array(
				'timeout'   => 10,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$headers = wp_remote_retrieve_headers( $response );

		if ( isset( $headers['server'] ) ) {
			$server_value = $headers['server'];

			if ( preg_match( '/\d+\.\d+/i', $server_value ) ) {
				return array(
					'id'            => 'security-server-version-exposed',
					'title'         => 'Server Version Exposed',
					'description'   => sprintf( 'Server header reveals version: "%s". Attackers can target known vulnerabilities in specific versions.', $server_value )
					'kb_link' => 'https://wpshadow.com/kb/server-header/',
					'training_link' => 'https://wpshadow.com/training/information-disclosure/',
					'auto_fixable'  => false,
					'threat_level'  => 35,
					'module'        => 'Security',
					'priority'      => 3,
					'meta'          => array( 'server_value' => $server_value ),
				);
			}
		}

		return null;
	}

	public static function get_name(): string {
		return __( 'Server Version Exposure', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks if the Server header leaks version information.', 'wpshadow' );
	}
}
