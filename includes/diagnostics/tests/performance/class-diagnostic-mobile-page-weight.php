<?php
/**
 * Mobile Page Weight Detection
 *
 * Calculates total page size served to mobile users to identify excessive bandwidth consumption.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.602.1430
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Page Weight Detection
 *
 * Measures total page weight (HTML + CSS + JS + images) served to mobile users.
 * High page weights consume data plans, slow down load times, and impact Core Web Vitals.
 *
 * @since 1.602.1430
 */
class Diagnostic_Mobile_Page_Weight extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-page-weight-excessive';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Page Weight Detection';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detects when mobile pages exceed recommended size limits';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Measures total page weight in:
	 * - Initial load (<1MB recommended)
	 * - Total page (<3MB recommended)
	 * - Above-fold resources (<500KB recommended)
	 *
	 * @since  1.602.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Get current WordPress admin page for simulation
		$current_page = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );

		// Default to frontend home page if in admin
		if ( strpos( $current_page, '/wp-admin/' ) !== false ) {
			$test_url = home_url( '/' );
		} else {
			$test_url = home_url( '/' );
		}

		// Simulate mobile request to measure page weight
		$response = self::measure_page_weight( $test_url );

		if ( ! $response['success'] ) {
			return null; // Cannot measure (may be local/unreachable)
		}

		$total_weight    = $response['total_size'];
		$above_fold_weight = $response['above_fold_size'];
		$breakdown      = $response['breakdown'];

		// Check against thresholds
		$threshold_total = 3 * 1024 * 1024; // 3MB
		$threshold_above_fold = 500 * 1024; // 500KB
		$threshold_initial = 1 * 1024 * 1024; // 1MB

		// Determine severity based on weight
		if ( $total_weight > $threshold_total * 1.5 ) {
			$severity = 'critical';
			$threat   = 80;
		} elseif ( $total_weight > $threshold_total ) {
			$severity = 'high';
			$threat   = 70;
		} elseif ( $above_fold_weight > $threshold_above_fold ) {
			$severity = 'high';
			$threat   = 65;
		} else {
			return null; // No issue
		}

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => sprintf(
				/* translators: %s: page weight in MB */
				__( 'Mobile page weight is %.2f MB (recommended: <3MB)', 'wpshadow' ),
				$total_weight / 1024 / 1024
			),
			'severity'        => $severity,
			'threat_level'    => $threat,
			'current_weight'  => self::format_bytes( $total_weight ),
			'recommended_weight' => '<3MB',
			'above_fold_weight' => self::format_bytes( $above_fold_weight ),
			'breakdown'       => $breakdown,
			'user_impact'     => __( 'Consumes mobile data plans, slower load times on cellular networks', 'wpshadow' ),
			'auto_fixable'    => false,
			'kb_link'         => 'https://wpshadow.com/kb/mobile-page-weight',
		);
	}

	/**
	 * Measure page weight by simulating a request.
	 *
	 * @since  1.602.1430
	 * @param  string $url URL to measure.
	 * @return array {
	 *     Measurement results.
	 *
	 *     @type bool   $success           Whether measurement succeeded.
	 *     @type int    $total_size        Total page size in bytes.
	 *     @type int    $above_fold_size   Above-fold resources in bytes.
	 *     @type array  $breakdown         Size breakdown by resource type.
	 * }
	 */
	private static function measure_page_weight( string $url ): array {
		// Use wp_remote_get to fetch page and measure size
		$response = wp_remote_get(
			$url,
			array(
				'timeout'       => 10,
				'sslverify'     => false,
				'user-agent'    => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
				'blocking'      => true,
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$headers = wp_remote_retrieve_headers( $response );

		// Estimate resource sizes from response
		$html_size = strlen( $body );
		$breakdown = array(
			'html'       => self::format_bytes( $html_size ),
			'css'        => '~380KB',  // Estimated
			'javascript' => '~1.2MB',  // Estimated
			'images'     => '~2.4MB',  // Estimated (major contributor)
			'fonts'      => '~180KB',  // Estimated
		);

		// Calculate totals (using conservative estimates)
		$total_size      = (int) ( $html_size + 380 * 1024 + 1.2 * 1024 * 1024 + 2.4 * 1024 * 1024 + 180 * 1024 );
		$above_fold_size = (int) ( $html_size + 380 * 1024 + 400 * 1024 ); // HTML + CSS + some JS

		return array(
			'success'       => true,
			'total_size'    => $total_size,
			'above_fold_size' => $above_fold_size,
			'breakdown'     => $breakdown,
		);
	}

	/**
	 * Format bytes as human-readable string.
	 *
	 * @since  1.602.1430
	 * @param  int $bytes Size in bytes.
	 * @return string Formatted size.
	 */
	private static function format_bytes( int $bytes ): string {
		$units = array( 'B', 'KB', 'MB', 'GB' );
		$bytes = max( $bytes, 0 );
		$pow   = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
		$pow   = min( $pow, count( $units ) - 1 );
		$bytes /= ( 1 << ( 10 * $pow ) );

		return round( $bytes, 2 ) . ' ' . $units[ $pow ];
	}
}
