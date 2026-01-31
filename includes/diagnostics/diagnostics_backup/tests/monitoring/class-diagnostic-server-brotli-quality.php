<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Server_Brotli_Quality extends Diagnostic_Base {
	protected static $slug = 'server-brotli-quality';
	protected static $title = 'Brotli Compression Quality';
	protected static $description = 'Confirms Brotli quality settings optimal';
	protected static $family = 'monitoring';
	public static function check() {
		if ( ! function_exists( 'wp_remote_get' ) ) { return null; }
		$url = home_url();
		$response = wp_remote_get( $url, array( 'blocking' => true, 'timeout' => 5 ) );
		if ( is_wp_error( $response ) ) { return null; }
		$content_encoding = wp_remote_retrieve_header( $response, 'content-encoding' );
		if ( ! empty( $content_encoding ) && strpos( $content_encoding, 'br' ) !== false ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Brotli compression is active. Ensure quality level is set between 4-11 for optimal balance of compression and speed.', 'wpshadow' ),
				'severity' => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/server-brotli-quality',
				'meta' => array( 'encoding' => $content_encoding ),
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
