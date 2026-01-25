<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Security_Mixed_Content extends Diagnostic_Base {


	protected static $slug        = 'test-security-mixed-content';
	protected static $title       = 'Mixed Content Test';
	protected static $description = 'Tests for mixed content (HTTP resources on HTTPS pages)';

	public static function check( ?string $url = null, ?string $html = null ): ?array {
		if ( $html !== null ) {
			return self::analyze_html( $html, $url ?? 'provided-html' );
		}

		$check_url = $url ?? home_url( '/' );

		// Only applicable for HTTPS sites
		if ( strpos( $check_url, 'https://' ) !== 0 ) {
			return null;
		}

		$html = self::fetch_html( $check_url );
		if ( $html === false ) {
			return null;
		}

		return self::analyze_html( $html, $check_url );
	}

	protected static function analyze_html( string $html, string $checked_url ): ?array {
		// Find HTTP resources (images, scripts, styles, iframes)
		$mixed_patterns = array(
			'/<img[^>]+src=["\']http:\/\/[^"\']+["\']/i',
			'/<script[^>]+src=["\']http:\/\/[^"\']+["\']/i',
			'/<link[^>]+href=["\']http:\/\/[^"\']+["\']/i',
			'/<iframe[^>]+src=["\']http:\/\/[^"\']+["\']/i',
		);

		$mixed_content_count = 0;
		$mixed_types         = array();

		if ( preg_match_all( $mixed_patterns[0], $html ) ) {
			$mixed_content_count += preg_match_all( $mixed_patterns[0], $html, $matches );
			$mixed_types[]        = 'images';
		}
		if ( preg_match_all( $mixed_patterns[1], $html ) ) {
			$mixed_content_count += preg_match_all( $mixed_patterns[1], $html, $matches );
			$mixed_types[]        = 'scripts';
		}
		if ( preg_match_all( $mixed_patterns[2], $html ) ) {
			$mixed_content_count += preg_match_all( $mixed_patterns[2], $html, $matches );
			$mixed_types[]        = 'stylesheets';
		}
		if ( preg_match_all( $mixed_patterns[3], $html ) ) {
			$mixed_content_count += preg_match_all( $mixed_patterns[3], $html, $matches );
			$mixed_types[]        = 'iframes';
		}

		if ( $mixed_content_count > 0 ) {
			return array(
				'id'            => 'security-mixed-content',
				'title'         => 'Mixed Content Detected',
				'description'   => sprintf(
					'%d HTTP resource(s) on HTTPS page (%s). Browsers block mixed content, breaking functionality and showing security warnings.',
					$mixed_content_count,
					implode( ', ', $mixed_types )
				)
				'kb_link' => 'https://wpshadow.com/kb/mixed-content/',
				'training_link' => 'https://wpshadow.com/training/https-security/',
				'auto_fixable'  => false,
				'threat_level'  => 60,
				'module'        => 'Security',
				'priority'      => 1,
				'meta'          => array(
					'mixed_count' => $mixed_content_count,
					'types'       => $mixed_types,
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
		return __( 'Mixed Content', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for mixed content (HTTP resources on HTTPS pages).', 'wpshadow' );
	}
}
