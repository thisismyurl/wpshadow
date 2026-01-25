<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Performance_Cache_Headers_Missing extends Diagnostic_Base {


	protected static $slug        = 'test-performance-cache-headers-missing';
	protected static $title       = 'Cache Headers Missing Test';
	protected static $description = 'Tests for missing Cache-Control and Expires headers.';

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

		$has_cache_control = isset( $headers['cache-control'] );
		$has_expires       = isset( $headers['expires'] );
		$has_etag          = isset( $headers['etag'] );

		if ( ! $has_cache_control && ! $has_expires ) {
			return array(
				'id'            => 'performance-no-cache-headers',
				'title'         => 'Missing Cache Headers',
				'description'   => 'No Cache-Control or Expires headers found. Browser caching can dramatically reduce repeat-visit load times.'
				'kb_link' => 'https://wpshadow.com/kb/browser-caching/',
				'training_link' => 'https://wpshadow.com/training/caching/',
				'auto_fixable'  => false,
				'threat_level'  => 45,
				'module'        => 'Performance',
				'priority'      => 2,
				'meta'          => array(
					'has_cache_control' => false,
					'has_expires'       => false,
					'has_etag'          => $has_etag,
				),
			);
		}

		return null;
	}

	public static function get_name(): string {
		return __( 'Cache Headers Missing', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for presence of Cache-Control or Expires headers.', 'wpshadow' );
	}
}
