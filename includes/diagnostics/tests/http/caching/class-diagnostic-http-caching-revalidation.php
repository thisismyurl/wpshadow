<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Http_Caching_Revalidation extends Diagnostic_Base {
	protected static $slug = 'http-caching-revalidation';
	protected static $title = 'HTTP Cache Revalidation';
	protected static $description = 'Confirms ETag and Last-Modified headers';
	protected static $family = 'caching';
	public static function check() {
		if ( ! function_exists( 'wp_remote_get' ) ) { return null; }
		$url = home_url();
		$response = wp_remote_get( $url, array( 'blocking' => true, 'timeout' => 5 ) );
		if ( is_wp_error( $response ) ) { return null; }
		$etag = wp_remote_retrieve_header( $response, 'etag' );
		$last_modified = wp_remote_retrieve_header( $response, 'last-modified' );
		if ( empty( $etag ) && empty( $last_modified ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Neither ETag nor Last-Modified headers found. Add these headers for better browser caching and revalidation.', 'wpshadow' ),
				'severity' => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/http-caching-revalidation',
				'meta' => array( 'etag' => $etag, 'last_modified' => $last_modified ),
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
