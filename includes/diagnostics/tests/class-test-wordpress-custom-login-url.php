<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_WordPress_Custom_Login_URL extends Diagnostic_Base {


	protected static $slug        = 'test-wordpress-custom-login-url';
	protected static $title       = 'Custom Login URL Test';
	protected static $description = 'Tests for custom login URL (wp-login.php hardening)';

	public static function check( ?string $url = null, ?string $html = null ): ?array {
		// Test if wp-login.php is directly accessible
		$login_url = home_url( '/wp-login.php' );

		$response = wp_remote_get(
			$login_url,
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$status_code = wp_remote_retrieve_response_code( $response );

		// If wp-login.php is accessible (200), it's the default URL
		if ( $status_code === 200 ) {
			return array(
				'id'            => 'wordpress-default-login-url',
				'title'         => 'Default Login URL Exposed',
				'description'   => 'wp-login.php is accessible at default location. Consider using a custom login URL to reduce brute-force attack surface.'
				'kb_link' => 'https://wpshadow.com/kb/custom-login-url/',
				'training_link' => 'https://wpshadow.com/training/login-hardening/',
				'auto_fixable'  => false,
				'threat_level'  => 30,
				'module'        => 'WordPress',
				'priority'      => 3,
				'meta'          => array( 'default_login_accessible' => true ),
			);
		}

		return null;
	}

	public static function get_name(): string {
		return __( 'Custom Login URL', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for custom login URL (wp-login.php hardening).', 'wpshadow' );
	}
}
