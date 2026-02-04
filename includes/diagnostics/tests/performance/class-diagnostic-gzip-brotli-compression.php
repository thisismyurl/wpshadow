<?php
/**
 * GZIP/Brotli Compression Diagnostic
 *
 * Checks if text compression is enabled for faster transfers.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2069
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GZIP/Brotli Compression Diagnostic Class
 *
 * Verifies text compression is enabled. Compression reduces
 * transfer size by 70-80% for text resources.
 *
 * @since 1.6033.2069
 */
class Diagnostic_GZIP_Brotli_Compression extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'gzip-brotli-compression';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'GZIP/Brotli Compression';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if text compression is enabled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests server response headers for compression.
	 * GZIP/Brotli can reduce transfer by 70-80%.
	 *
	 * @since  1.6033.2069
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Make request to home page with compression headers
		$response = wp_remote_get(
			home_url(),
			array(
				'timeout' => 5,
				'headers' => array(
					'Accept-Encoding' => 'gzip, deflate, br',
				),
			)
		);
		
		if ( is_wp_error( $response ) ) {
			return null; // Can't test
		}
		
		// Check response headers
		$headers = wp_remote_retrieve_headers( $response );
		$content_encoding = isset( $headers['content-encoding'] ) ? strtolower( $headers['content-encoding'] ) : '';
		
		// Check for compression
		$gzip_enabled   = strpos( $content_encoding, 'gzip' ) !== false;
		$brotli_enabled = strpos( $content_encoding, 'br' ) !== false;
		
		if ( $gzip_enabled || $brotli_enabled ) {
			return null; // Compression is enabled
		}
		
		// Check if output buffering might be compressing
		if ( ini_get( 'zlib.output_compression' ) ) {
			return null; // PHP-level compression
		}
		
		// Get uncompressed size estimate
		$body = wp_remote_retrieve_body( $response );
		$uncompressed_size = strlen( $body );
		$estimated_compressed = round( $uncompressed_size * 0.25 ); // ~75% reduction typical
		$potential_savings = $uncompressed_size - $estimated_compressed;
		
		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: potential savings, 2: uncompressed size */
				__( 'Text compression (GZIP/Brotli) is not enabled. Enabling compression could save %1$s per page load (%2$s uncompressed). Compression reduces HTML, CSS, and JavaScript transfer sizes by 70-80%%.', 'wpshadow' ),
				size_format( $potential_savings ),
				size_format( $uncompressed_size )
			),
			'severity'     => 'high',
			'threat_level' => 65,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/enable-gzip-compression',
			'meta'         => array(
				'gzip_enabled'         => false,
				'brotli_enabled'       => false,
				'uncompressed_size'    => $uncompressed_size,
				'estimated_compressed' => $estimated_compressed,
				'potential_savings'    => $potential_savings,
				'savings_percent'      => 75,
				'server_software'      => isset( $_SERVER['SERVER_SOFTWARE'] ) ? $_SERVER['SERVER_SOFTWARE'] : 'unknown',
			),
		);
	}
}
