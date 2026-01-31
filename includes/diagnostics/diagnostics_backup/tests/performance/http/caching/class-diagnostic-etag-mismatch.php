<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Etag_Mismatch extends Diagnostic_Base {
	protected static $slug = 'etag-mismatch';
	protected static $title = 'ETag Mismatch Detected';
	protected static $description = 'Detects inconsistent ETags';
	protected static $family = 'caching';
	public static function check() {
		if ( ! function_exists( 'wp_remote_get' ) ) { return null; }
		$url = home_url();
		$response1 = wp_remote_get( $url, array( 'blocking' => true, 'timeout' => 5 ) );
		if ( is_wp_error( $response1 ) ) { return null; }
		$etag1 = wp_remote_retrieve_header( $response1, 'etag' );
		sleep( 1 );
		$response2 = wp_remote_get( $url, array( 'blocking' => true, 'timeout' => 5 ) );
		if ( is_wp_error( $response2 ) ) { return null; }
		$etag2 = wp_remote_retrieve_header( $response2, 'etag' );
		if ( ! empty( $etag1 ) && ! empty( $etag2 ) && $etag1 !== $etag2 ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'ETag values differ between requests. This may indicate dynamic content generation or caching issues. Ensure consistent ETags for better caching.', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/etag-mismatch',
				'meta' => array(),
			);
		}

		// Basic WordPress functionality checks
		if ( ! function_exists( 'get_option' ) ) {
			$issues[] = __( 'Options API not available', 'wpshadow' );
		}
		if ( ! function_exists( 'add_action' ) ) {
			$issues[] = __( 'WordPress hooks not available', 'wpshadow' );
		}
		if ( empty( $GLOBALS['wpdb'] ) ) {
			$issues[] = __( 'Database not initialized', 'wpshadow' );
		}
		return null;
	}
}
