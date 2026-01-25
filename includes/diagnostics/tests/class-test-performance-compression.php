<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Performance_Compression extends Diagnostic_Base {


	protected static $slug        = 'test-performance-compression';
	protected static $title       = 'GZIP Compression Test';
	protected static $description = 'Tests for GZIP/Brotli compression on text assets';

	public static function check( ?string $url = null, ?string $html = null ): ?array {
		$check_url = $url ?? home_url( '/' );

		// Check for compression via headers
		$response = wp_remote_get(
			$check_url,
			array(
				'timeout'   => 10,
				'sslverify' => false,
				'headers'   => array( 'Accept-Encoding' => 'gzip, deflate, br' ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$headers          = wp_remote_retrieve_headers( $response );
		$content_encoding = isset( $headers['content-encoding'] ) ? strtolower( $headers['content-encoding'] ) : '';

		$has_compression = ( strpos( $content_encoding, 'gzip' ) !== false ||
			strpos( $content_encoding, 'br' ) !== false ||
			strpos( $content_encoding, 'deflate' ) !== false );

		if ( ! $has_compression ) {
			return array(
				'id'            => 'performance-no-compression',
				'title'         => 'No Text Compression',
				'description'   => 'HTML is not compressed (no gzip/brotli). Compression can reduce transfer size by 70-90%.'
				'kb_link' => 'https://wpshadow.com/kb/gzip-compression/',
				'training_link' => 'https://wpshadow.com/training/server-optimization/',
				'auto_fixable'  => false,
				'threat_level'  => 50,
				'module'        => 'Performance',
				'priority'      => 2,
				'meta'          => array(
					'content_encoding' => $content_encoding,
					'checked_url'      => $check_url,
				),
			);
		}

		return null;
	}

	public static function get_name(): string {
		return __( 'Text Compression', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for GZIP/Brotli compression on text assets.', 'wpshadow' );
	}
}
