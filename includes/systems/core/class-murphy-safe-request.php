<?php
/**
 * Murphy-Safe Request Handler
 *
 * Defensive network request wrapper that implements Murphy's Law principles:
 * - Assume networks will fail
 * - Fail gracefully with cached fallbacks
 * - Never lose user data
 * - Recover automatically when possible
 *
 * @package    WPShadow
 * @subpackage Core
 * @since      1.6035.1500
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Murphy_Safe_Request Class
 *
 * Provides resilient network request handling with automatic fallbacks,
 * retry logic, and graceful degradation.
 *
 * Philosophy Alignment:
 * - ⚙️ Murphy's Law: Assume everything will fail
 * - #8 Inspire Confidence: Users trust the system won't break
 * - #1 Helpful Neighbor: Transparent about what's happening
 *
 * @since 1.6035.1500
 */
class Murphy_Safe_Request {

	/**
	 * Fetch data with comprehensive fallback strategy
	 *
	 * Fallback order:
	 * 1. Fresh cache (if exists and valid)
	 * 2. Live API request
	 * 3. Stale cache (better than nothing)
	 * 4. Safe default
	 *
	 * @since  1.6035.1500
	 * @param  string $url       URL to fetch.
	 * @param  string $cache_key Cache key for storing response.
	 * @param  int    $ttl       Cache TTL in seconds. Default 1 hour.
	 * @param  array  $args      Optional wp_remote_get arguments.
	 * @return array Response data with metadata.
	 */
	public static function fetch_with_fallback( $url, $cache_key, $ttl = 3600, $args = array() ) {
		// Try 1: Get cached data (fastest, most reliable).
		$cached = get_transient( $cache_key );
		if ( false !== $cached && is_array( $cached ) ) {
			$cached['_source'] = 'cache';
			$cached['_cached_at'] = get_transient( "{$cache_key}_timestamp" );
			return $cached;
		}

		// Try 2: Make API request with timeout.
		$defaults = array(
			'timeout'     => 5,
			'redirection' => 2,
			'user-agent'  => 'WPShadow/' . WPSHADOW_VERSION,
		);

		$max_tries   = isset( $args['max_tries'] ) ? absint( $args['max_tries'] ) : 2;
		$retry_delay = isset( $args['retry_delay'] ) ? absint( $args['retry_delay'] ) : 1;

		unset( $args['max_tries'], $args['retry_delay'] );

		$args = wp_parse_args( $args, $defaults );

		$response = null;
		$attempt  = 0;
		while ( $attempt < max( 1, $max_tries ) ) {
			$attempt++;
			$response = wp_remote_get( $url, $args );

			if ( ! self::is_retryable_response( $response ) ) {
				break;
			}

			// Wait before retry (exponential backoff).
			sleep( max( 1, $retry_delay ) );
			$retry_delay *= 2;
		}

		// Handle failure gracefully.
		if ( is_wp_error( $response ) ) {
			// Log the error for debugging.
			Error_Handler::log_info(
				'Network request failed, attempting fallback',
				array(
					'url'   => $url,
					'error' => $response->get_error_message(),
					'attempts' => $attempt,
				)
			);

			// Try 3: Get stale cache (better than nothing).
			$stale = get_option( "{$cache_key}_backup", array() );
			if ( ! empty( $stale ) && is_array( $stale ) ) {
				$stale['_source'] = 'stale_cache';
				$stale['_stale']  = true;
				$stale['_warning'] = __( 'Using cached data. Service temporarily unavailable.', 'wpshadow' );
				return $stale;
			}

			// Try 4: Return safe default.
			return array(
				'_source'  => 'default',
				'_error'   => true,
				'_message' => __( 'Service temporarily unavailable. Please try again later.', 'wpshadow' ),
				'_original_error' => $response->get_error_message(),
			);
		}

		// Validate response code.
		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $code ) {
			Error_Handler::log_info(
				'Non-200 response code received',
				array(
					'url'  => $url,
					'code' => $code,
				)
			);

			// Try stale cache on non-200.
			$stale = get_option( "{$cache_key}_backup", array() );
			if ( ! empty( $stale ) && is_array( $stale ) ) {
				$stale['_source'] = 'stale_cache';
				$stale['_stale']  = true;
				$stale['_warning'] = sprintf(
					/* translators: %d: HTTP status code */
					__( 'Service returned error (HTTP %d). Using cached data.', 'wpshadow' ),
					$code
				);
				return $stale;
			}

			return array(
				'_source'  => 'default',
				'_error'   => true,
				'_message' => sprintf(
					/* translators: %d: HTTP status code */
					__( 'Service returned error (HTTP %d). Please try again later.', 'wpshadow' ),
					$code
				),
				'_code'    => $code,
			);
		}

		// Parse response body.
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! is_array( $data ) ) {
			Error_Handler::log_warning(
				'Invalid JSON response received',
				array(
					'url'  => $url,
					'body' => substr( $body, 0, 200 ),
				)
			);

			// Try stale cache on parse error.
			$stale = get_option( "{$cache_key}_backup", array() );
			if ( ! empty( $stale ) && is_array( $stale ) ) {
				$stale['_source'] = 'stale_cache';
				$stale['_stale']  = true;
				$stale['_warning'] = __( 'Service returned invalid data. Using cached data.', 'wpshadow' );
				return $stale;
			}

			return array(
				'_source'  => 'default',
				'_error'   => true,
				'_message' => __( 'Service returned invalid data. Please try again later.', 'wpshadow' ),
			);
		}

		// Success! Cache the response.
		$data['_source']     = 'live';
		$data['_fetched_at'] = current_time( 'timestamp' );

		set_transient( $cache_key, $data, $ttl );
		set_transient( "{$cache_key}_timestamp", current_time( 'mysql' ), $ttl );

		// Keep a backup copy (no expiration).
		update_option( "{$cache_key}_backup", $data, false );

		return $data;
	}

	/**
	 * POST request with retry logic
	 *
	 * Attempts to POST data with exponential backoff retry.
	 * Queues failed requests for later retry if all attempts fail.
	 *
	 * @since  1.6035.1500
	 * @param  string $url        URL to POST to.
	 * @param  array  $body       Request body.
	 * @param  int    $max_tries  Maximum retry attempts. Default 3.
	 * @param  array  $args       Optional wp_remote_post arguments.
	 * @return array|WP_Error Response or error.
	 */
	public static function post_with_retry( $url, $body, $max_tries = 3, $args = array() ) {
		$defaults = array(
			'timeout'     => 10,
			'redirection' => 2,
			'user-agent'  => 'WPShadow/' . WPSHADOW_VERSION,
			'body'        => $body,
		);
		$args = wp_parse_args( $args, $defaults );

		$attempt = 0;
		$delay   = 1; // Start with 1 second delay.

		while ( $attempt < $max_tries ) {
			$attempt++;

			$response = wp_remote_post( $url, $args );

			// Success!
			if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
				return $response;
			}

			/**
			 * Determine if a response should be retried.
			 *
			 * Retries on network errors and transient HTTP status codes.
			 *
			 * @since  1.8035.1200
			 * @param  mixed $response Response from wp_remote_get.
			 * @return bool True if retryable.
			 */
			private static function is_retryable_response( $response ) : bool {
				if ( is_wp_error( $response ) ) {
					return true;
				}

				$code = wp_remote_retrieve_response_code( $response );
				if ( in_array( $code, array( 408, 429 ), true ) ) {
					return true;
				}

				if ( $code >= 500 && $code <= 599 ) {
					return true;
				}

				return false;
			}

			// Last attempt failed.
			if ( $attempt >= $max_tries ) {
				// Queue for later retry.
				self::queue_failed_request( $url, $body, $args );

				$error_msg = is_wp_error( $response ) ? $response->get_error_message() : 'HTTP ' . wp_remote_retrieve_response_code( $response );

				Error_Handler::log_warning(
					'POST request failed after all retries, queued for later',
					array(
						'url'      => $url,
						'attempts' => $attempt,
						'error'    => $error_msg,
					)
				);

				return $response;
			}

			// Wait before retry (exponential backoff).
			sleep( $delay );
			$delay *= 2; // 1s, 2s, 4s.
		}

		return $response;
	}

	/**
	 * Queue a failed request for later retry
	 *
	 * Stores failed requests in database for retry worker to process.
	 *
	 * @since  1.6035.1500
	 * @param  string $url  Request URL.
	 * @param  array  $body Request body.
	 * @param  array  $args Request args.
	 * @return bool True if queued successfully.
	 */
	private static function queue_failed_request( $url, $body, $args ) {
		$queue = get_option( 'wpshadow_request_retry_queue', array() );

		$queue[] = array(
			'url'        => $url,
			'body'       => $body,
			'args'       => $args,
			'queued_at'  => current_time( 'timestamp' ),
			'attempts'   => 0,
			'max_attempts' => 5,
		);

		// Limit queue size to prevent memory issues.
		if ( count( $queue ) > 100 ) {
			array_shift( $queue ); // Remove oldest.
		}

		return update_option( 'wpshadow_request_retry_queue', $queue, false );
	}

	/**
	 * Process retry queue (called by cron)
	 *
	 * Attempts to resend failed requests. Called by wp-cron every 5 minutes.
	 *
	 * @since  1.6035.1500
	 * @return array {
	 *     Processing results.
	 *
	 *     @type int $processed Count of processed requests.
	 *     @type int $succeeded Count of successful retries.
	 *     @type int $failed    Count of failed retries.
	 *     @type int $removed   Count of removed (too old/too many attempts).
	 * }
	 */
	public static function process_retry_queue() {
		$queue = get_option( 'wpshadow_request_retry_queue', array() );

		if ( empty( $queue ) ) {
			return array(
				'processed' => 0,
				'succeeded' => 0,
				'failed'    => 0,
				'removed'   => 0,
			);
		}

		$stats      = array(
			'processed' => 0,
			'succeeded' => 0,
			'failed'    => 0,
			'removed'   => 0,
		);
		$new_queue  = array();
		$now        = current_time( 'timestamp' );

		foreach ( $queue as $request ) {
			$stats['processed']++;

			// Remove if too old (7 days).
			if ( $now - $request['queued_at'] > 7 * DAY_IN_SECONDS ) {
				$stats['removed']++;
				continue;
			}

			// Remove if too many attempts.
			if ( $request['attempts'] >= $request['max_attempts'] ) {
				$stats['removed']++;
				Error_Handler::log_info(
					'Request removed from retry queue after max attempts',
					array( 'url' => $request['url'] )
				);
				continue;
			}

			// Attempt to resend.
			$response = wp_remote_post( $request['url'], $request['args'] );

			if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
				$stats['succeeded']++;
				// Don't re-add to queue.
				continue;
			}

			// Failed, increment attempts and re-queue.
			$request['attempts']++;
			$new_queue[] = $request;
			$stats['failed']++;
		}

		// Update queue.
		update_option( 'wpshadow_request_retry_queue', $new_queue, false );

		return $stats;
	}

	/**
	 * Register retry queue cron job
	 *
	 * @since 1.6035.1500
	 * @return void
	 */
	public static function register_cron() {
		if ( ! wp_next_scheduled( 'wpshadow_process_retry_queue' ) ) {
			wp_schedule_event( time(), 'wpshadow_5min', 'wpshadow_process_retry_queue' );
		}

		add_action( 'wpshadow_process_retry_queue', array( __CLASS__, 'process_retry_queue' ) );
	}

	/**
	 * Add custom cron interval
	 *
	 * @since  1.6035.1500
	 * @param  array $schedules Existing cron schedules.
	 * @return array Modified schedules.
	 */
	public static function add_cron_interval( $schedules ) {
		$schedules['wpshadow_5min'] = array(
			'interval' => 5 * MINUTE_IN_SECONDS,
			'display'  => __( 'Every 5 Minutes (WPShadow)', 'wpshadow' ),
		);

		return $schedules;
	}
}
