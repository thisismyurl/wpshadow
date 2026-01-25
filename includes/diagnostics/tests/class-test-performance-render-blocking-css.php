<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Performance_Render_Blocking_CSS extends Diagnostic_Base {


	protected static $slug        = 'test-performance-render-blocking-css';
	protected static $title       = 'Render-Blocking CSS Test';
	protected static $description = 'Tests for render-blocking CSS resources';

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
		// Count render-blocking stylesheets (in <head>, no media/print, no async)
		preg_match_all( '/<link[^>]+rel=["\']stylesheet["\'][^>]*>/i', $html, $matches );

		$blocking_count = 0;
		$blocking_urls  = array();

		foreach ( $matches[0] as $link ) {
			// Skip if has media print or async attributes
			if ( preg_match( '/media=["\']print["\']/i', $link ) ) {
				continue;
			}
			if ( preg_match( '/media=["\'][^"\']*\(prefers-color-scheme/i', $link ) ) {
				continue; // Media query
			}

			++$blocking_count;

			if ( preg_match( '/href=["\']([^"\']+)["\']/i', $link, $url_match ) ) {
				$blocking_urls[] = basename( $url_match[1] );
			}
		}

		if ( $blocking_count === 0 ) {
			return null; // PASS
		}

		if ( $blocking_count <= 2 ) {
			return null; // Acceptable - 1-2 stylesheets is normal
		}

		$threat_level = 40;
		if ( $blocking_count > 5 ) {
			$threat_level = 60;
		}

		return array(
			'id'            => 'performance-render-blocking-css',
			'title'         => 'Multiple Render-Blocking Stylesheets',
			'description'   => sprintf(
				'Found %d render-blocking CSS files. Each stylesheet delays page rendering. Consider consolidating or deferring non-critical styles.',
				$blocking_count
			)
			'kb_link' => 'https://wpshadow.com/kb/render-blocking-css/',
			'training_link' => 'https://wpshadow.com/training/performance-optimization/',
			'auto_fixable'  => false,
			'threat_level'  => $threat_level,
			'module'        => 'Performance',
			'priority'      => 2,
			'meta'          => array(
				'blocking_count' => $blocking_count,
				'sample_files'   => array_slice( $blocking_urls, 0, 5 ),
				'checked_url'    => $checked_url,
			),
		);
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
		return __( 'Render-Blocking CSS', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for excessive render-blocking CSS files.', 'wpshadow' );
	}
}
