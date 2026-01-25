<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Performance_CDN extends Diagnostic_Base {


	protected static $slug        = 'test-performance-cdn';
	protected static $title       = 'CDN Usage Test';
	protected static $description = 'Tests for Content Delivery Network implementation';

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
		$site_domain = parse_url( home_url( '/' ), PHP_URL_HOST );

		// Look for CDN domains
		$cdn_patterns = array(
			'cloudflare',
			'cloudfront',
			'fastly',
			'cdn\.',
			'akamai',
			'maxcdn',
			'rackcdn',
			'bunnycdn',
			'stackpath',
		);

		$has_cdn = false;
		foreach ( $cdn_patterns as $pattern ) {
			if ( preg_match( '/' . $pattern . '/i', $html ) ) {
				$has_cdn = true;
				break;
			}
		}

		// Count static resources (images, CSS, JS)
		preg_match_all( '/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $images );
		preg_match_all( '/<link[^>]+href=["\']([^"\']+\.css)["\']/i', $html, $css );
		preg_match_all( '/<script[^>]+src=["\']([^"\']+\.js)["\']/i', $html, $js );

		$total_resources = count( $images[0] ) + count( $css[0] ) + count( $js[0] );

		// If many resources but no CDN
		if ( $total_resources > 15 && ! $has_cdn ) {
			return array(
				'id'            => 'performance-no-cdn',
				'title'         => 'No CDN Detected',
				'description'   => sprintf( '%d static resources found but no CDN usage. CDN can reduce latency by serving assets from geographically closer servers.', $total_resources )
				'kb_link' => 'https://wpshadow.com/kb/cdn-setup/',
				'training_link' => 'https://wpshadow.com/training/performance-optimization/',
				'auto_fixable'  => false,
				'threat_level'  => 25,
				'module'        => 'Performance',
				'priority'      => 3,
				'meta'          => array(
					'total_resources' => $total_resources,
					'site_domain'     => $site_domain,
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
		return __( 'CDN Usage', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for Content Delivery Network implementation.', 'wpshadow' );
	}
}
