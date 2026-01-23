<?php
/**
 * AJAX Response Optimizer for WPShadow
 *
 * Optimizes AJAX responses:
 * - Response compression
 * - Minimal payloads
 * - Caching headers
 *
 * Philosophy: Ridiculously Good (#7) - Fast responses = happy users
 * 
 * @package WPShadow
 * @subpackage Admin
 */

declare(strict_types=1);

namespace WPShadow\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX Response Optimizer class
 */
class AJAX_Response_Optimizer {
	
	/**
	 * Initialize optimizer
	 */
	public static function init(): void {
		// Compress AJAX responses
		add_action( 'wp_ajax_wpshadow_quick_scan', [ __CLASS__, 'enable_compression' ], 1 );
		add_action( 'wp_ajax_wpshadow_deep_scan', [ __CLASS__, 'enable_compression' ], 1 );
		add_action( 'wp_ajax_wpshadow_first_scan', [ __CLASS__, 'enable_compression' ], 1 );
		
		// Add cache headers for cacheable responses
		add_action( 'wp_ajax_wpshadow_get_tooltip_catalog', [ __CLASS__, 'add_cache_headers' ], 1 );
		add_action( 'wp_ajax_wpshadow_get_kb_article', [ __CLASS__, 'add_cache_headers' ], 1 );
	}
	
	/**
	 * Enable gzip compression for AJAX responses
	 */
	public static function enable_compression(): void {
		// Only if not already compressed
		if ( ! headers_sent() && ! ob_get_length() ) {
			if ( extension_loaded( 'zlib' ) && ! ini_get( 'zlib.output_compression' ) ) {
				ob_start( 'ob_gzhandler' );
			}
		}
	}
	
	/**
	 * Add cache headers for cacheable AJAX responses
	 */
	public static function add_cache_headers(): void {
		if ( headers_sent() ) {
			return;
		}
		
		// Cache for 1 hour
		header( 'Cache-Control: public, max-age=3600' );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + 3600 ) . ' GMT' );
	}
	
	/**
	 * Minify JSON response (remove whitespace)
	 *
	 * @param mixed $data Data to encode
	 * @return string JSON string
	 */
	public static function minify_json( $data ): string {
		return wp_json_encode( $data, JSON_UNESCAPED_SLASHES );
	}
	
	/**
	 * Send optimized success response
	 *
	 * @param array $data Response data
	 */
	public static function send_success( array $data ): void {
		self::enable_compression();
		wp_send_json_success( $data );
	}
	
	/**
	 * Send optimized error response
	 *
	 * @param string $message Error message
	 * @param int    $code Error code
	 */
	public static function send_error( string $message, int $code = 400 ): void {
		self::enable_compression();
		wp_send_json_error( [ 'message' => $message ], $code );
	}
}

// Initialize optimizer
AJAX_Response_Optimizer::init();
