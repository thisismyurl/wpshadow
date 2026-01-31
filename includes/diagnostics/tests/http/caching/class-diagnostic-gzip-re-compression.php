<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Gzip_Re_Compression extends Diagnostic_Base {
	protected static $slug = 'gzip-re-compression';
	protected static $title = 'Double Gzip Compression Detected';
	protected static $description = 'Detects double-compressed responses';
	protected static $family = 'caching';
	public static function check() {
		if ( ! function_exists( 'wp_remote_get' ) ) { return null; }
		$url = home_url();
		$response = wp_remote_get( $url, array( 'blocking' => true, 'timeout' => 5 ) );
		if ( is_wp_error( $response ) ) { return null; }
		$content_encoding = wp_remote_retrieve_header( $response, 'content-encoding' );
		if ( ! empty( $content_encoding ) && strpos( $content_encoding, 'gzip' ) !== false ) {
			$body = wp_remote_retrieve_body( $response );
			if ( strpos( $body, "\x1f\x8b" ) === 0 ) {
				return array(
					'id' => self::$slug,
					'title' => self::$title,
					'description' => __( 'Response appears to be double-compressed (gzip over gzip). This wastes bandwidth. Check compression settings in web server or PHP.', 'wpshadow' ),
					'severity' => 'medium',
					'threat_level' => 25,
					'auto_fixable' => false,
					'kb_link' => 'https://wpshadow.com/kb/gzip-re-compression',
					'meta' => array( 'encoding' => $content_encoding ),
				);
			}
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
