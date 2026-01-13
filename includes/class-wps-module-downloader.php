<?php
/**
 * Robust Module Downloader
 *
 * Handles resilient ZIP downloads with retry, checksum verification, progress tracking,
 * and graceful failure handling.
 *
 * @package wp_support_SUPPORT
 * @since 1.2601.74000
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Module Downloader with resilience features.
 */
class WPS_Module_Downloader {

	/**
	 * Download session ID for progress tracking.
	 *
	 * @var string
	 */
	private string $session_id;

	/**
	 * Maximum retry attempts.
	 *
	 * @var int
	 */
	private int $max_retries = 3;

	/**
	 * Initial backoff delay in seconds.
	 *
	 * @var int
	 */
	private int $initial_backoff = 2;

	/**
	 * Download timeout in seconds.
	 *
	 * @var int
	 */
	private int $timeout = 60;

	/**
	 * Constructor.
	 *
	 * @param string $session_id Optional session ID for progress tracking.
	 */
	public function __construct( string $session_id = '' ) {
		$this->session_id = ! empty( $session_id ) ? $session_id : $this->generate_session_id();
	}

	/**
	 * Generate unique session ID.
	 *
	 * @return string
	 */
	private function generate_session_id(): string {
		return 'wps_dl_' . wp_generate_password( 12, false );
	}

	/**
	 * Get session ID.
	 *
	 * @return string
	 */
	public function get_session_id(): string {
		return $this->session_id;
	}

	/**
	 * Download package with resilience (retry, progress, checksum).
	 *
	 * @param string      $url           Download URL.
	 * @param string      $destination   Destination file path.
	 * @param string|null $expected_hash Expected SHA-256 hash (optional).
	 * @return true|WP_Error True on success, WP_Error on failure.
	 */
	public function download( string $url, string $destination, ?string $expected_hash = null ) {
		// Validate URL.
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return new WP_Error( 'invalid_url', __( 'Invalid download URL.', 'plugin-wp-support-thisismyurl' ) );
		}

		// Ensure destination directory exists.
		$dest_dir = dirname( $destination );
		if ( ! is_dir( $dest_dir ) && ! wp_mkdir_p( $dest_dir ) ) {
			return new WP_Error( 'mkdir_failed', __( 'Could not create destination directory.', 'plugin-wp-support-thisismyurl' ) );
		}

		// Initialize progress.
		$this->update_progress( 0, 'starting', __( 'Starting download...', 'plugin-wp-support-thisismyurl' ) );

		// Attempt download with retry logic.
		$attempt    = 0;
		$last_error = null;

		while ( $attempt < $this->max_retries ) {
			++$attempt;

			$this->update_progress(
				0,
				'downloading',
				sprintf(
					/* translators: %d: Attempt number */
					__( 'Download attempt %d...', 'plugin-wp-support-thisismyurl' ),
					$attempt
				)
			);

			$result = $this->attempt_download( $url, $destination );

			if ( true === $result ) {
				// Success - verify checksum if provided.
				if ( ! empty( $expected_hash ) ) {
					$verify_result = $this->verify_checksum( $destination, $expected_hash );
					if ( is_wp_error( $verify_result ) ) {
						$this->cleanup_file( $destination );
						$this->log_failure( $url, 'checksum_mismatch', $verify_result->get_error_message() );
						return $verify_result;
					}
				}

				$this->update_progress( 100, 'complete', __( 'Download complete.', 'plugin-wp-support-thisismyurl' ) );
				$this->log_success( $url, $attempt );
				return true;
			}

			// Download failed, store error and apply backoff.
			$last_error = $result;
			$this->log_failure( $url, 'download_attempt_' . $attempt, $result->get_error_message() );

			if ( $attempt < $this->max_retries ) {
				$backoff_seconds = $this->calculate_backoff( $attempt );
				$this->update_progress(
					0,
					'retrying',
					sprintf(
						/* translators: %d: Wait time in seconds */
						__( 'Retrying in %d seconds...', 'plugin-wp-support-thisismyurl' ),
						$backoff_seconds
					)
				);
				sleep( $backoff_seconds );
			}
		}

		// All retries exhausted.
		$this->cleanup_file( $destination );
		$this->update_progress( 0, 'failed', __( 'Download failed after all retries.', 'plugin-wp-support-thisismyurl' ) );
		$this->log_failure( $url, 'exhausted_retries', $last_error ? $last_error->get_error_message() : 'Unknown error' );

		return $last_error ?? new WP_Error( 'download_failed', __( 'Download failed.', 'plugin-wp-support-thisismyurl' ) );
	}

	/**
	 * Attempt a single download.
	 *
	 * @param string $url         Download URL.
	 * @param string $destination Destination file path.
	 * @return true|WP_Error True on success, WP_Error on failure.
	 */
	private function attempt_download( string $url, string $destination ): bool|WP_Error {
		// Use WordPress HTTP API with streaming.
		$response = wp_remote_get(
			$url,
			array(
				'timeout'  => $this->timeout,
				'stream'   => true,
				'filename' => $destination,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$http_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $http_code && 206 !== $http_code ) {
			return new WP_Error(
				'http_error',
				sprintf(
					/* translators: %d: HTTP status code */
					__( 'HTTP error: %d', 'plugin-wp-support-thisismyurl' ),
					$http_code
				)
			);
		}

		// Verify file was created and has content.
		if ( ! file_exists( $destination ) || 0 === filesize( $destination ) ) {
			return new WP_Error( 'download_incomplete', __( 'Downloaded file is empty or missing.', 'plugin-wp-support-thisismyurl' ) );
		}

		return true;
	}

	/**
	 * Verify file checksum.
	 *
	 * @param string $file_path     Path to file.
	 * @param string $expected_hash Expected SHA-256 hash.
	 * @return true|WP_Error True if valid, WP_Error on mismatch.
	 */
	private function verify_checksum( string $file_path, string $expected_hash ): bool|WP_Error {
		if ( ! file_exists( $file_path ) ) {
			return new WP_Error( 'file_not_found', __( 'File not found for checksum verification.', 'plugin-wp-support-thisismyurl' ) );
		}

		$actual_hash = hash_file( 'sha256', $file_path );
		if ( false === $actual_hash ) {
			return new WP_Error( 'hash_failed', __( 'Could not calculate file hash.', 'plugin-wp-support-thisismyurl' ) );
		}

		if ( ! hash_equals( strtolower( $expected_hash ), strtolower( $actual_hash ) ) ) {
			$remediation = sprintf(
				/* translators: 1: Expected hash, 2: Actual hash */
				__( 'Checksum mismatch. Expected: %1$s, Got: %2$s. Please try downloading again or contact support if the issue persists.', 'plugin-wp-support-thisismyurl' ),
				substr( $expected_hash, 0, 16 ) . '...',
				substr( $actual_hash, 0, 16 ) . '...'
			);
			return new WP_Error( 'checksum_mismatch', $remediation );
		}

		return true;
	}

	/**
	 * Calculate exponential backoff delay.
	 *
	 * @param int $attempt Attempt number (1-based).
	 * @return int Delay in seconds.
	 */
	private function calculate_backoff( int $attempt ): int {
		// Exponential backoff: 2^(attempt-1) * initial_backoff, capped at 30s.
		$delay = pow( 2, $attempt - 1 ) * $this->initial_backoff;
		return min( $delay, 30 );
	}

	/**
	 * Update download progress.
	 *
	 * @param int    $percent Progress percentage (0-100).
	 * @param string $status  Status key (starting, downloading, retrying, complete, failed).
	 * @param string $message Human-readable message.
	 * @return void
	 */
	private function update_progress( int $percent, string $status, string $message ): void {
		$progress = array(
			'percent' => max( 0, min( 100, $percent ) ),
			'status'  => sanitize_key( $status ),
			'message' => sanitize_text_field( $message ),
			'time'    => time(),
		);

		$transient_key = 'wps_dl_progress_' . $this->session_id;
		set_transient( $transient_key, $progress, 5 * MINUTE_IN_SECONDS );
	}

	/**
	 * Get current progress.
	 *
	 * @return array Progress data.
	 */
	public function get_progress(): array {
		$transient_key = 'wps_dl_progress_' . $this->session_id;
		$progress      = get_transient( $transient_key );

		if ( false === $progress ) {
			return array(
				'percent' => 0,
				'status'  => 'unknown',
				'message' => __( 'No progress data available.', 'plugin-wp-support-thisismyurl' ),
				'time'    => time(),
			);
		}

		return $progress;
	}

	/**
	 * Clear progress data.
	 *
	 * @return void
	 */
	public function clear_progress(): void {
		$transient_key = 'wps_dl_progress_' . $this->session_id;
		delete_transient( $transient_key );
	}

	/**
	 * Clean up downloaded file.
	 *
	 * @param string $file_path Path to file.
	 * @return void
	 */
	private function cleanup_file( string $file_path ): void {
		if ( file_exists( $file_path ) ) {
			wp_delete_file( $file_path );
		}
	}

	/**
	 * Log download success.
	 *
	 * @param string $url      Download URL.
	 * @param int    $attempts Number of attempts taken.
	 * @return void
	 */
	private function log_success( string $url, int $attempts ): void {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}

		$message = sprintf(
			'[WPS Module Downloader] Successfully downloaded %s after %d attempt(s)',
			esc_url_raw( $url ),
			$attempts
		);

		error_log( $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log

		// Optional audit event.
		do_action( 'WPS_module_download_success', $url, $attempts, $this->session_id );
	}

	/**
	 * Log download failure.
	 *
	 * @param string $url    Download URL.
	 * @param string $reason Failure reason.
	 * @param string $detail Error details.
	 * @return void
	 */
	private function log_failure( string $url, string $reason, string $detail ): void {
		$message = sprintf(
			'[WPS Module Downloader] Failed to download %s - Reason: %s - Detail: %s',
			esc_url_raw( $url ),
			sanitize_key( $reason ),
			sanitize_text_field( $detail )
		);

		error_log( $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log

		// Optional audit event.
		do_action( 'WPS_module_download_failure', $url, $reason, $detail, $this->session_id );
	}

	/**
	 * Validate ZIP file integrity and structure.
	 *
	 * @param string $zip_path Path to ZIP file.
	 * @param string $expected_slug Expected module slug.
	 * @return true|WP_Error True if valid, WP_Error on failure.
	 */
	public function validate_zip( string $zip_path, string $expected_slug ): bool|WP_Error {
		// Check file exists.
		if ( ! file_exists( $zip_path ) ) {
			return new WP_Error( 'zip_not_found', __( 'ZIP file not found.', 'plugin-wp-support-thisismyurl' ) );
		}

		// Check ZIP extension is available.
		if ( ! class_exists( '\ZipArchive' ) ) {
			return new WP_Error( 'no_zip_ext', __( 'ZIP extension not available.', 'plugin-wp-support-thisismyurl' ) );
		}

		$zip = new \ZipArchive();
		$res = $zip->open( $zip_path, \ZipArchive::RDONLY );

		if ( true !== $res ) {
			return new WP_Error(
				'bad_zip',
				sprintf(
					/* translators: %d: ZIP error code */
					__( 'Could not open ZIP file (error code: %d). The file may be corrupt.', 'plugin-wp-support-thisismyurl' ),
					$res
				)
			);
		}

		// Validate ZIP contains expected structure.
		$valid_structure = false;
		$num_files       = $zip->numFiles;

		for ( $i = 0; $i < $num_files; $i++ ) {
			$stat = $zip->statIndex( $i );
			if ( false === $stat ) {
				continue;
			}

			$name = $stat['name'];

			// Look for slug/slug.php or slug-main/slug-main.php (GitHub archive pattern).
			if ( preg_match( '#^([^/]+)/\1\.php$#', $name, $matches ) ||
				preg_match( '#^(' . preg_quote( $expected_slug, '#' ) . '[^/]*)/[^/]+\.php$#', $name ) ) {
				$valid_structure = true;
				break;
			}
		}

		$zip->close();

		if ( ! $valid_structure ) {
			return new WP_Error(
				'invalid_structure',
				sprintf(
					/* translators: %s: Expected slug */
					__( 'ZIP does not contain expected plugin structure (%s/%s.php). The archive may be invalid.', 'plugin-wp-support-thisismyurl' ),
					$expected_slug,
					$expected_slug
				)
			);
		}

		return true;
	}

	/**
	 * Get remediation guidance for common errors.
	 *
	 * @param WP_Error $error Error object.
	 * @return string Actionable guidance.
	 */
	public static function get_error_guidance( WP_Error $error ): string {
		$code = $error->get_error_code();

		$guidance_map = array(
			'invalid_url'         => __( 'The download URL is invalid. Please refresh the catalog and try again.', 'plugin-wp-support-thisismyurl' ),
			'http_error'          => __( 'The server returned an error. Please check your internet connection and try again.', 'plugin-wp-support-thisismyurl' ),
			'download_incomplete' => __( 'The download did not complete. Please check your internet connection and try again.', 'plugin-wp-support-thisismyurl' ),
			'checksum_mismatch'   => __( 'The downloaded file is corrupt. Please try again or contact support if the issue persists.', 'plugin-wp-support-thisismyurl' ),
			'bad_zip'             => __( 'The ZIP file is corrupt. Please try downloading again.', 'plugin-wp-support-thisismyurl' ),
			'invalid_structure'   => __( 'The downloaded plugin has an unexpected structure. Please contact the plugin author.', 'plugin-wp-support-thisismyurl' ),
			'exhausted_retries'   => __( 'Download failed after multiple attempts. Please check your internet connection or try again later.', 'plugin-wp-support-thisismyurl' ),
		);

		return $guidance_map[ $code ] ?? __( 'An unexpected error occurred. Please try again.', 'plugin-wp-support-thisismyurl' );
	}
}

/* @changelog WPS_Module_Downloader class created for resilient module downloads. */
