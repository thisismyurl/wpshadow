<?php
/**
 * Amplitude Analytics Event Ingestion Diagnostic
 *
 * Amplitude Analytics Event Ingestion misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1386.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Amplitude Analytics Event Ingestion Diagnostic Class
 *
 * @since 1.1386.0000
 */
class Diagnostic_AmplitudeAnalyticsEventIngestion extends Diagnostic_Base {

	protected static $slug = 'amplitude-analytics-event-ingestion';
	protected static $title = 'Amplitude Analytics Event Ingestion';
	protected static $description = 'Amplitude Analytics Event Ingestion misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		$has_amplitude = defined( 'AMPLITUDE_API_KEY' ) ||
		                 get_option( 'amplitude_api_key', '' ) ||
		                 function_exists( 'amplitude_track_event' );

		if ( ! $has_amplitude ) {
			return null;
		}

		$issues = array();

		// Check 1: Event batching.
		$batch_enabled = get_option( 'amplitude_batch_events', '0' );
		if ( '0' === $batch_enabled ) {
			$issues[] = 'event batching disabled (increased API calls)';
		}

		// Check 2: Max events per batch.
		$batch_size = get_option( 'amplitude_max_batch_size', 50 );
		if ( $batch_size < 10 ) {
			$issues[] = "batch size {$batch_size} too small (inefficient batching)";
		} elseif ( $batch_size > 500 ) {
			$issues[] = "batch size {$batch_size} too large (memory risk)";
		}

		// Check 3: Event queue size.
		$queue_size = get_option( 'amplitude_queue_size', 100 );
		if ( $queue_size < 50 ) {
			$issues[] = "event queue only holds {$queue_size} events (may lose data)";
		}

		// Check 4: Failed event retry.
		$retry = get_option( 'amplitude_retry_failed_events', '0' );
		if ( '0' === $retry ) {
			$issues[] = 'failed events not retried (data loss)';
		}

		// Check 5: Event validation.
		$validate = get_option( 'amplitude_validate_events', '1' );
		if ( '0' === $validate ) {
			$issues[] = 'event validation disabled (invalid data accepted)';
		}

		// Check 6: API rate limits.
		$api_key = get_option( 'amplitude_api_key', '' );
		if ( empty( $api_key ) ) {
			$issues[] = 'Amplitude API key not configured';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 50 + ( count( $issues ) * 4 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Amplitude event ingestion issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/amplitude-analytics-event-ingestion',
			);
		}

		return null;
	}
}
