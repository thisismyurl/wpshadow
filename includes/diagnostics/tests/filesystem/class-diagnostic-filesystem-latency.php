<?php
/**
 * Filesystem Latency Diagnostic
 *
 * Measures read/write latency on wp-content directory.
 *
 * @since   1.2601.2112
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Filesystem_Latency
 *
 * Tests read/write latency on WordPress filesystem to detect slow storage or network issues.
 *
 * @since 1.2601.2112
 */
class Diagnostic_Filesystem_Latency extends Diagnostic_Base {

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2112
	 * @return array|null Finding array if issues detected, null otherwise.
	 */
	public static function check() {
		if ( ! is_admin() ) {
			return null;
		}

		$content_dir = WP_CONTENT_DIR;
		if ( ! is_writable( $content_dir ) ) {
			return null; // Can't test if directory isn't writable.
		}

		// Write test: measure time to write 1MB.
		$test_file = $content_dir . '/.wpshadow-latency-test-' . wp_rand( 10000, 99999 ) . '.tmp';
		$test_data = str_repeat( 'x', 1024 * 1024 ); // 1MB.

		$start = microtime( true );
		$result = file_put_contents( $test_data, $test_file );
		$write_time = microtime( true ) - $start;

		if ( false === $result ) {
			return null; // Can't write, skip test.
		}

		// Read test.
		$start     = microtime( true );
		$read_data = file_get_contents( $test_file );
		$read_time = microtime( true ) - $start;

		// Cleanup.
		@unlink( $test_file );

		// Alert if latency is excessive (write > 1 second or read > 500ms for 1MB).
		if ( $write_time > 1.0 || $read_time > 0.5 ) {
			return array(
				'id'           => 'filesystem-latency',
				'title'        => __( 'High Filesystem Latency Detected', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %1$s: write time, %2$s: read time */
					__( 'Filesystem read/write latency is high: Write=%.3fs, Read=%.3fs (for 1MB test). This may indicate slow storage, network filesystem issues, or server performance problems affecting site speed.', 'wpshadow' ),
					$write_time,
					$read_time
				),
				'severity'     => 'medium',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/filesystem_latency',
				'meta'         => array(
					'write_time_ms' => round( $write_time * 1000, 2 ),
					'read_time_ms'  => round( $read_time * 1000, 2 ),
					'test_size_mb'  => 1,
				),
			);
		}

		return null;
	}
}
