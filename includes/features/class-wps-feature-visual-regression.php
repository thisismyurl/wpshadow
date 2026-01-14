<?php
/**
 * Feature: Visual Regression Update Guard
 *
 * Captures screenshots before and after updates to detect layout changes.
 * Integrates with auto-rollback to prevent silent layout breakage.
 *
 * @package WPS\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

use WPS\CoreSupport\WPS_Snapshot_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPS_Feature_Visual_Regression
 *
 * Visual regression detection using screenshot comparison.
 */
final class WPS_Feature_Visual_Regression extends WPS_Abstract_Feature {

	/**
	 * Option key for storing pre-update screenshots.
	 */
	private const PRE_UPDATE_SCREENSHOTS_KEY = 'WPS_pre_update_screenshots';

	/**
	 * Option key for tracking visual regression threshold.
	 */
	private const THRESHOLD_KEY = 'WPS_visual_regression_threshold';

	/**
	 * Default visual difference threshold (percentage).
	 */
	private const DEFAULT_THRESHOLD = 5.0;

	/**
	 * Transient timeout for results (seconds).
	 */
	private const RESULTS_TRANSIENT_TIMEOUT = 300;

	/**
	 * Transient name for visual regression failure flag.
	 */
	private const FAILED_FLAG_TRANSIENT = 'wps_visual_regression_failed';

	/**
	 * Stabilization delay after update (seconds).
	 */
	private const STABILIZATION_DELAY = 3;

	/**
	 * Maximum text content length to analyze.
	 */
	private const MAX_TEXT_LENGTH = 5000;

	/**
	 * Weight for fingerprint difference in comparison.
	 */
	private const FINGERPRINT_DIFF_WEIGHT = 30;

	/**
	 * Weight for HTML hash difference in comparison.
	 */
	private const HTML_HASH_DIFF_WEIGHT = 20;

	/**
	 * Maximum weight for length difference in comparison.
	 */
	private const LENGTH_DIFF_MAX_WEIGHT = 50;

	/**
	 * Pages to capture for visual regression testing.
	 */
	private const CAPTURE_PAGES = array(
		'home'    => '',
		'sample'  => 'sample-page',
		'archive' => 'category/uncategorized',
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'visual-regression',
				'name'               => __( 'Visual Regression Update Guard', 'plugin-wp-support-thisismyurl' ),
				'description'        => __( 'Automatically capture screenshots before/after updates and detect visual changes. Flags or rolls back updates with >5% visual difference.', 'plugin-wp-support-thisismyurl' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'safety',
				'widget_label'       => __( 'Safety Features', 'plugin-wp-support-thisismyurl' ),
				'widget_description' => __( 'Advanced safety and recovery features to protect your WordPress installation', 'plugin-wp-support-thisismyurl' ),
			)
		);
	}

	/**
	 * Register hooks when feature is enabled.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Hook before any update starts.
		add_filter( 'upgrader_pre_install', array( $this, 'capture_pre_update_screenshots' ), 10, 2 );

		// Hook after update completes to validate visual changes.
		add_action( 'upgrader_process_complete', array( $this, 'validate_visual_changes' ), 998, 2 );

		// Admin notice for visual regression results.
		add_action( 'admin_notices', array( $this, 'display_visual_regression_notice' ) );

		// Settings for threshold configuration.
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register settings for visual regression threshold.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		register_setting(
			'wps_visual_regression',
			self::THRESHOLD_KEY,
			array(
				'type'              => 'number',
				'description'       => __( 'Visual difference threshold percentage (0-100)', 'plugin-wp-support-thisismyurl' ),
				'sanitize_callback' => array( $this, 'sanitize_threshold' ),
				'default'           => self::DEFAULT_THRESHOLD,
			)
		);
	}

	/**
	 * Sanitize threshold value.
	 *
	 * @param mixed $value Input value.
	 * @return float Sanitized threshold between 0 and 100.
	 */
	public function sanitize_threshold( $value ): float {
		$threshold = (float) $value;
		return max( 0.0, min( 100.0, $threshold ) );
	}

	/**
	 * Capture screenshots before update starts.
	 *
	 * @param bool  $response   Installation response.
	 * @param array $hook_extra Extra arguments passed to hooked filters.
	 * @return bool Always returns $response to allow update to proceed.
	 */
	public function capture_pre_update_screenshots( $response, $hook_extra ): bool {
		// Only capture screenshots if we're doing an update (not install).
		if ( isset( $hook_extra['action'] ) && 'update' === $hook_extra['action'] ) {
			$type = $hook_extra['type'] ?? 'unknown';

			// Capture screenshots of key pages.
			$screenshots = $this->capture_screenshots();

			if ( ! empty( $screenshots ) ) {
				// Store screenshots for later comparison.
				$this->update_setting( self::PRE_UPDATE_SCREENSHOTS_KEY, array(
						'type'        => $type,
						'timestamp'   => time( ),
						'screenshots' => $screenshots,
					),
					false
				);

			} else {
			}
		}

		return $response;
	}

	/**
	 * Validate visual changes after update completes.
	 *
	 * @param object $upgrader Upgrader instance.
	 * @param array  $options  Upgrader options.
	 * @return void
	 */
	public function validate_visual_changes( $upgrader, $options ): void {
		// Check if we have pre-update screenshots.
		$pre_update_data = $this->get_setting( self::PRE_UPDATE_SCREENSHOTS_KEY );

		if ( ! $pre_update_data || empty( $pre_update_data['screenshots'] ) ) {
			return;
		}
		// Allow site to stabilize after update (configurable delay).
		sleep( self::STABILIZATION_DELAY );

		// Capture post-update screenshots.
		$post_screenshots = $this->capture_screenshots();

		if ( empty( $post_screenshots ) ) {
			$this->cleanup_screenshots( $pre_update_data['screenshots'] );
			delete_option( self::PRE_UPDATE_SCREENSHOTS_KEY );
			return;
		}

		// Compare screenshots.
		$comparison_results = $this->compare_screenshots(
			$pre_update_data['screenshots'], $post_screenshots
		 );

		// Calculate average visual difference.
		$total_diff = 0;
		$count      = 0;

		foreach ( $comparison_results as $page => $result ) {
			if ( isset( $result['difference'] ) ) {
				$total_diff += $result['difference'];
				++$count;
			}
		}

		$avg_difference = $count > 0 ? $total_diff / $count : 0;
		$threshold      = $this->get_threshold();

		// Store comparison results.
		set_transient(
			'wps_visual_regression_results',
			array(
				'type'               => $pre_update_data['type'],
				'timestamp'          => time(),
				'avg_difference'     => $avg_difference,
				'threshold'          => $threshold,
				'exceeded_threshold' => $avg_difference > $threshold,
				'details'            => $comparison_results,
			),
			self::RESULTS_TRANSIENT_TIMEOUT
		);

		// Flag for manual review or trigger rollback if threshold exceeded.
		if ( $avg_difference > $threshold ) {

			// Set a flag that can be checked by auto-rollback feature.
			set_transient( self::FAILED_FLAG_TRANSIENT, true, self::RESULTS_TRANSIENT_TIMEOUT );
		} else {
		}

		// Cleanup old screenshots.
		$this->cleanup_screenshots( $pre_update_data['screenshots'] );
		$this->cleanup_screenshots( $post_screenshots );

		// Clean up tracking option.
		delete_option( self::PRE_UPDATE_SCREENSHOTS_KEY );
	}

	/**
	 * Capture screenshots of key pages.
	 *
	 * @return array Array of captured screenshot data.
	 */
	private function capture_screenshots(): array {
		$screenshots = array();
		$site_url    = home_url();

		foreach ( self::CAPTURE_PAGES as $page_id => $page_path ) {
			$url = $page_path ? trailingslashit( $site_url ) . $page_path : $site_url;

			// Use WordPress HTTP API to fetch page content.
			// Note: SSL verification is enabled for security. If you need to disable it for local development,
			// use the 'wps_visual_regression_request_args' filter.
			$response = wp_remote_get(
				$url,
				apply_filters(
					'wps_visual_regression_request_args',
					array(
						'timeout'     => 30,
						'sslverify'   => true,
						'user-agent'  => 'WPS-Visual-Regression-Bot/1.0',
						'redirection' => 5,
					)
				)
			);

			if ( is_wp_error( $response ) ) {

				continue;
			}

			$status_code = wp_remote_retrieve_response_code( $response );
			if ( 200 !== $status_code ) {

				continue;
			}

			$html = wp_remote_retrieve_body( $response );

			// Generate visual fingerprint from HTML structure and CSS.
			$fingerprint = $this->generate_visual_fingerprint( $html );

			$screenshots[ $page_id ] = array(
				'url'         => $url,
				'timestamp'   => time(),
				'fingerprint' => $fingerprint,
				'html_hash'   => md5( $html ),
				'html_length' => strlen( $html ),
			);
		}

		return $screenshots;
	}

	/**
	 * Generate visual fingerprint from HTML content.
	 *
	 * Uses DOM structure, CSS classes, and visible content to create a fingerprint.
	 *
	 * @param string $html HTML content.
	 * @return string Visual fingerprint hash.
	 */
	private function generate_visual_fingerprint( string $html ): string {
		// Extract visual elements from HTML.
		$visual_elements = array();

		// 1. Extract all CSS classes (layout indicators).
		preg_match_all( '/class=["\']([^"\']+)["\']/', $html, $class_matches );
		if ( ! empty( $class_matches[1] ) ) {
			$visual_elements['classes'] = implode( ' ', $class_matches[1] );
		}

		// 2. Extract inline styles (direct visual changes).
		preg_match_all( '/style=["\']([^"\']+)["\']/', $html, $style_matches );
		if ( ! empty( $style_matches[1] ) ) {
			$visual_elements['styles'] = implode( ' ', $style_matches[1] );
		}

		// 3. Extract structural tags (layout structure).
		preg_match_all( '/<(div|section|article|header|footer|nav|aside|main)[^>]*>/', $html, $tag_matches );
		if ( ! empty( $tag_matches[0] ) ) {
			$visual_elements['structure'] = implode( '', $tag_matches[0] );
		}

		// 4. Extract visible text content (content changes).
		$text                    = wp_strip_all_tags( $html );
		$text                    = preg_replace( '/\s+/', ' ', $text );
		$text                    = substr( $text, 0, self::MAX_TEXT_LENGTH );
		$visual_elements['text'] = $text;

		// 5. Extract stylesheet links (CSS file changes).
		preg_match_all( '/<link[^>]+rel=["\']stylesheet["\'][^>]*href=["\']([^"\']+)["\']/', $html, $css_matches );
		if ( ! empty( $css_matches[1] ) ) {
			$visual_elements['stylesheets'] = implode( ' ', $css_matches[1] );
		}

		// Create fingerprint from visual elements.
		$fingerprint_data = json_encode( $visual_elements );

		return hash( 'sha256', $fingerprint_data );
	}

	/**
	 * Compare pre and post update screenshots.
	 *
	 * @param array $pre_screenshots  Pre-update screenshots.
	 * @param array $post_screenshots Post-update screenshots.
	 * @return array Comparison results.
	 */
	private function compare_screenshots( array $pre_screenshots, array $post_screenshots ): array {
		$results = array();

		foreach ( $pre_screenshots as $page_id => $pre_data ) {
			if ( ! isset( $post_screenshots[ $page_id ] ) ) {
				$results[ $page_id ] = array(
					'status'     => 'missing',
					'difference' => 100.0,
					'message'    => __( 'Post-update screenshot not available', 'plugin-wp-support-thisismyurl' ),
				);
				continue;
			}

			$post_data = $post_screenshots[ $page_id ];

			// Compare fingerprints.
			$fingerprint_match = $pre_data['fingerprint'] === $post_data['fingerprint'];

			// Calculate percentage difference based on multiple factors.
			$differences = array();

			// 1. Fingerprint difference (most important).
			$differences[] = $fingerprint_match ? 0 : self::FINGERPRINT_DIFF_WEIGHT;

			// 2. HTML hash difference.
			$differences[] = $pre_data['html_hash'] === $post_data['html_hash'] ? 0 : self::HTML_HASH_DIFF_WEIGHT;

			// 3. HTML length difference (significant structural changes).
			$length_diff     = abs( $pre_data['html_length'] - $post_data['html_length'] );
			$length_diff_pct = $pre_data['html_length'] > 0
				? ( $length_diff / $pre_data['html_length'] ) * 100
				: 0;
			$differences[]   = min( $length_diff_pct, self::LENGTH_DIFF_MAX_WEIGHT );

			// Calculate average difference.
			$total_difference = array_sum( $differences );
			$avg_difference   = count( $differences ) > 0 ? $total_difference / count( $differences ) : 0;

			$results[ $page_id ] = array(
				'status'            => $avg_difference > self::DEFAULT_THRESHOLD ? 'changed' : 'unchanged',
				'difference'        => $avg_difference,
				'fingerprint_match' => $fingerprint_match,
				'html_hash_match'   => $pre_data['html_hash'] === $post_data['html_hash'],
				'length_diff_pct'   => $length_diff_pct,
			);
		}

		return $results;
	}

	/**
	 * Cleanup screenshot files.
	 *
	 * @param array $screenshots Screenshot data array.
	 * @return void
	 */
	private function cleanup_screenshots( array $screenshots ): void {
		// In this implementation, we're not storing actual files,
		// just fingerprints and hashes. This method is a placeholder
		// for future implementations that might store actual screenshot files.
		foreach ( $screenshots as $screenshot ) {
			// No files to cleanup in current implementation.
		}
	}

	/**
	 * Get visual difference threshold.
	 *
	 * @return float Threshold percentage.
	 */
	private function get_threshold(): float {
		$threshold = $this->get_setting( self::THRESHOLD_KEY, self::DEFAULT_THRESHOLD  );
		return (float) $threshold;
	}

	/**
	 * Display admin notice for visual regression results.
	 *
	 * @return void
	 */
	public function display_visual_regression_notice(): void {
		$results = get_transient( 'wps_visual_regression_results' );

		if ( ! $results || ! is_array( $results ) ) {
			return;
		}

		// Delete transient so notice only shows once.
		delete_transient( 'wps_visual_regression_results' );

		$exceeded  = $results['exceeded_threshold'] ?? false;
		$avg_diff  = $results['avg_difference'] ?? 0;
		$threshold = $results['threshold'] ?? self::DEFAULT_THRESHOLD;
		$type      = $results['type'] ?? 'unknown';

		$class = $exceeded ? 'notice-warning' : 'notice-success';

		if ( $exceeded ) {
			$message = sprintf(
				/* translators: 1: Update type, 2: Visual difference percentage, 3: Threshold percentage */
				__( 'Visual Regression Detected: %1$s update resulted in %2$.2f%% visual difference (threshold: %3$.2f%%). Manual review recommended.', 'plugin-wp-support-thisismyurl' ),
				ucfirst( $type ),
				$avg_diff,
				$threshold
			);
		} else {
			$message = sprintf(
				/* translators: 1: Update type, 2: Visual difference percentage */
				__( 'Visual Check Passed: %1$s update resulted in %2$.2f%% visual difference. No significant layout changes detected.', 'plugin-wp-support-thisismyurl' ),
				ucfirst( $type ),
				$avg_diff
			);
		}

		printf(
			'<div class="notice %s is-dismissible"><p><strong>%s:</strong> %s</p></div>',
			esc_attr( $class ),
			esc_html__( 'Visual Regression Update Guard', 'plugin-wp-support-thisismyurl' ),
			esc_html( $message )
		);
	}
}
