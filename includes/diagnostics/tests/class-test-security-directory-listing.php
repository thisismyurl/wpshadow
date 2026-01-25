<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Security_Directory_Listing extends Diagnostic_Base {


	protected static $slug        = 'test-security-directory-listing';
	protected static $title       = 'Directory Listing Test';
	protected static $description = 'Tests for exposed directory listings';

	public static function check( ?string $url = null, ?string $html = null ): ?array {
		$site_url = home_url( '/' );

		// Test common directories
		$test_dirs = array(
			'wp-content/uploads/',
			'wp-content/plugins/',
			'wp-content/themes/',
			'wp-includes/',
		);

		$exposed_dirs = array();

		foreach ( $test_dirs as $dir ) {
			$test_url = rtrim( $site_url, '/' ) . '/' . $dir;
			$response = wp_remote_get(
				$test_url,
				array(
					'timeout'   => 5,
					'sslverify' => false,
				)
			);

			if ( is_wp_error( $response ) ) {
				continue;
			}

			$body = wp_remote_retrieve_body( $response );

			// Check for directory listing indicators
			if (
				preg_match( '/<title>Index of|Directory listing for|Parent Directory/i', $body ) ||
				preg_match( '/<h1>Index of/i', $body )
			) {
				$exposed_dirs[] = $dir;
			}
		}

		if ( ! empty( $exposed_dirs ) ) {
			return array(
				'id'            => 'security-directory-listing',
				'title'         => 'Directory Listing Enabled',
				'description'   => sprintf(
					'%d director%s exposed: %s. Directory listings reveal site structure and files to attackers.',
					count( $exposed_dirs ),
					count( $exposed_dirs ) === 1 ? 'y' : 'ies',
					implode( ', ', $exposed_dirs )
				)
				'kb_link' => 'https://wpshadow.com/kb/directory-listing/',
				'training_link' => 'https://wpshadow.com/training/server-hardening/',
				'auto_fixable'  => false,
				'threat_level'  => 55,
				'module'        => 'Security',
				'priority'      => 2,
				'meta'          => array( 'exposed_dirs' => $exposed_dirs ),
			);
		}

		return null;
	}

	public static function get_name(): string {
		return __( 'Directory Listing', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for exposed directory listings.', 'wpshadow' );
	}
}
