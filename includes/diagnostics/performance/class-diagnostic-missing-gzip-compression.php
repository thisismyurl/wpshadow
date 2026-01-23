<?php
/**
 * Diagnostic: Missing Gzip Compression
 *
 * Detects if gzip compression is not enabled for faster page loads.
 *
 * Philosophy: Ridiculously Good (#7) - Free performance wins
 * KB Link: https://wpshadow.com/kb/missing-gzip-compression
 * Training: https://wpshadow.com/training/missing-gzip-compression
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Missing Gzip Compression diagnostic
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Missing_Gzip_Compression extends Diagnostic_Base {

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Diagnostic result or null if no issue
	 */
	public static function check(): ?array {
		// Check if zlib extension is available
		if ( ! extension_loaded( 'zlib' ) ) {
			return [
				'id'                => 'missing-gzip-compression',
				'title'             => __( 'Gzip Compression Not Available', 'wpshadow' ),
				'description'       => __( 'The zlib PHP extension is not installed. This prevents gzip compression, which can reduce page sizes by 70%. Contact your hosting provider to enable the zlib extension.', 'wpshadow' ),
				'severity'          => 'high',
				'category'          => 'performance',
				'impact'            => 'high',
				'effort'            => 'low',
				'kb_link'           => 'https://wpshadow.com/kb/missing-gzip-compression',
				'training_link'     => 'https://wpshadow.com/training/missing-gzip-compression',
				'affected_resource' => 'PHP configuration',
				'metadata'          => [
					'zlib_available'   => false,
					'potential_saving' => '70% size reduction',
				],
			];
		}

		// Check if already enabled via PHP ini
		if ( ini_get( 'zlib.output_compression' ) ) {
			return null; // Already enabled
		}

		// Check if enabled via Apache/Nginx (test with a small request)
		$test_response = self::test_compression();
		
		if ( $test_response['compressed'] ) {
			return null; // Compression working
		}

		$description = __( 'Gzip compression is not enabled on your server. Enabling compression can reduce page sizes by 70% and significantly speed up your site. WPShadow\'s AJAX Response Optimizer automatically compresses AJAX responses, but server-level compression is recommended for all pages.', 'wpshadow' );

		return [
			'id'                => 'missing-gzip-compression',
			'title'             => __( 'Gzip Compression Not Enabled', 'wpshadow' ),
			'description'       => $description,
			'severity'          => 'medium',
			'category'          => 'performance',
			'impact'            => 'high',
			'effort'            => 'medium',
			'kb_link'           => 'https://wpshadow.com/kb/missing-gzip-compression',
			'training_link'     => 'https://wpshadow.com/training/missing-gzip-compression',
			'affected_resource' => 'All pages',
			'metadata'          => [
				'zlib_available'   => true,
				'server_compression' => false,
				'potential_saving' => '70% size reduction',
				'test_url'         => home_url(),
			],
		];
	}

	/**
	 * Test if compression is working
	 *
	 * @return array Test result
	 */
	private static function test_compression(): array {
		$test_url = add_query_arg( 'wpshadow_compression_test', '1', home_url() );
		
		$response = wp_remote_get( $test_url, [
			'headers' => [
				'Accept-Encoding' => 'gzip, deflate',
			],
			'timeout' => 5,
		] );

		if ( is_wp_error( $response ) ) {
			return [ 'compressed' => false ];
		}

		$headers = wp_remote_retrieve_headers( $response );
		$content_encoding = $headers['content-encoding'] ?? '';

		return [
			'compressed'       => ( strpos( $content_encoding, 'gzip' ) !== false ),
			'content_encoding' => $content_encoding,
		];
	}

}