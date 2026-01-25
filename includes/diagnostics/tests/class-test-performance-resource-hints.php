<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Performance_Resource_Hints extends Diagnostic_Base {


	protected static $slug        = 'test-performance-resource-hints';
	protected static $title       = 'Resource Hints Test';
	protected static $description = 'Tests for resource hints (prefetch, preload, preconnect)';

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
		// Check for resource hints
		$has_preconnect   = preg_match( '/<link[^>]+rel=["\']preconnect["\']/i', $html );
		$has_dns_prefetch = preg_match( '/<link[^>]+rel=["\']dns-prefetch["\']/i', $html );
		$has_preload      = preg_match( '/<link[^>]+rel=["\']preload["\']/i', $html );

		// Check for external resources that could benefit
		$has_external_resources = preg_match( '/fonts\.googleapis\.com|cdn\.|cloudflare|jquery\.com|gstatic/i', $html );

		if ( $has_external_resources && ! $has_preconnect && ! $has_dns_prefetch ) {
			return array(
				'id'            => 'performance-missing-resource-hints',
				'title'         => 'Missing Resource Hints',
				'description'   => 'External resources detected but no preconnect/dns-prefetch hints. Resource hints can reduce latency by 100-500ms.'
				'kb_link' => 'https://wpshadow.com/kb/resource-hints/',
				'training_link' => 'https://wpshadow.com/training/performance-optimization/',
				'auto_fixable'  => false,
				'threat_level'  => 30,
				'module'        => 'Performance',
				'priority'      => 3,
				'meta'          => array(
					'has_external_resources' => true,
					'has_hints'              => false,
				),
			);
		}

		return null;
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
		return __( 'Resource Hints', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for resource hints (prefetch, preload, preconnect).', 'wpshadow' );
	}
}
