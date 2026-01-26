<?php
/**
 * Diagnostic: Environment Compression Enabled
 *
 * Checks if GZIP/compression is enabled on the server to reduce bandwidth
 * and improve page load times.
 *
 * Category: Environment & Infrastructure
 * Priority: Medium
 * Philosophy: 9 (Everything Has a KPI - bandwidth & performance impact)
 *
 * Test Description:
 * Verifies that text compression (GZIP/Brotli/Deflate) is enabled on the server.
 * Compression can reduce transfer size by 70-90% for text assets like HTML, CSS, and JS.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_EnvCompressionEnabled Class
 *
 * Detects when server compression is not configured, which can significantly
 * impact site performance and bandwidth costs.
 *
 * @since 1.2601.2148
 */
class Diagnostic_EnvCompressionEnabled extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'env-compression-enabled';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Server Compression Enabled';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies that text compression is enabled to reduce bandwidth and improve performance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'environment';

	/**
	 * Display name for the family
	 *
	 * @var string
	 */
	protected static $family_label = 'Environment & Infrastructure';

	/**
	 * Run the diagnostic check
	 *
	 * Checks for compression in two ways:
	 * 1. PHP zlib.output_compression setting
	 * 2. Actual HTTP response headers from the site
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if compression not enabled, null otherwise.
	 */
	public static function check(): ?array {
		// First check PHP configuration.
		$php_compression_enabled = false;
		if ( function_exists( 'ini_get' ) ) {
			$zlib_compression        = ini_get( 'zlib.output_compression' );
			$php_compression_enabled = ! empty( $zlib_compression ) && '0' !== $zlib_compression && 'off' !== strtolower( (string) $zlib_compression );
		}

		// Check actual HTTP response headers.
		$http_compression_enabled = self::check_http_compression();

		// If either method shows compression is enabled, we're good.
		if ( $php_compression_enabled || $http_compression_enabled ) {
			return null;
		}

		// Compression is not enabled - return finding.
		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => __( 'Text compression (GZIP/Brotli) is not enabled on your server. Enabling compression can reduce page size by 70-90%, improving load times and reducing bandwidth costs.', 'wpshadow' ),
			'severity'      => 'medium',
			'threat_level'  => 60,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/enable-compression',
			'training_link' => 'https://wpshadow.com/training/server-optimization',
			'category'      => 'Environment & Infrastructure',
			'meta'          => array(
				'php_compression'  => $php_compression_enabled,
				'http_compression' => $http_compression_enabled,
				'recommendation'   => __( 'Contact your hosting provider to enable GZIP compression or configure it in your .htaccess file.', 'wpshadow' ),
			),
		);
	}

	/**
	 * Check if HTTP compression is enabled by making a test request
	 *
	 * @since  1.2601.2148
	 * @return bool True if compression headers detected, false otherwise.
	 */
	private static function check_http_compression(): bool {
		// Make a request to the home page with compression headers.
		$response = wp_remote_get(
			home_url( '/' ),
			array(
				'timeout'   => 10,
				'sslverify' => false,
				'headers'   => array(
					'Accept-Encoding' => 'gzip, deflate, br',
				),
			)
		);

		// If request failed, we can't determine compression status.
		if ( is_wp_error( $response ) ) {
			return false;
		}

		// Check for compression in response headers.
		$headers = wp_remote_retrieve_headers( $response );
		if ( ! $headers ) {
			return false;
		}

		$content_encoding = isset( $headers['content-encoding'] ) ? strtolower( (string) $headers['content-encoding'] ) : '';

		// Check for common compression types.
		return ( false !== strpos( $content_encoding, 'gzip' ) ||
				false !== strpos( $content_encoding, 'br' ) ||
				false !== strpos( $content_encoding, 'deflate' ) );
	}
}
