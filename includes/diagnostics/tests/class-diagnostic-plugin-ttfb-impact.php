<?php
/**
 * Plugin TTFB Impact Diagnostic
 *
 * Identifies plugins that significantly delay Time To First Byte (TTFB).
 * Part of Performance Attribution analysis to help site owners understand
 * which plugins are impacting site speed.
 *
 * @package WPShadow\Diagnostics
 * @since   1.2601.2148
 */

declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Plugin_Ttfb_Impact Class
 *
 * Detects plugins that add significant delays to Time To First Byte.
 * This helps identify performance bottlenecks at the plugin level.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Plugin_Ttfb_Impact extends Diagnostic_Base {
	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-ttfb-impact';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin TTFB Impact';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies plugins that significantly delay Time To First Byte (TTFB)';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'performance_attribution';

	/**
	 * Diagnostic family label
	 *
	 * @var string
	 */
	protected static $family_label = 'Performance Attribution';

	/**
	 * TTFB threshold in milliseconds for medium severity
	 *
	 * @var int
	 */
	const THRESHOLD_MEDIUM = 100;

	/**
	 * TTFB threshold in milliseconds for high severity
	 *
	 * @var int
	 */
	const THRESHOLD_HIGH = 300;

	/**
	 * Get diagnostic ID
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic identifier.
	 */
	public static function get_id(): string {
		return 'plugin-ttfb-impact';
	}

	/**
	 * Get diagnostic name
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic name.
	 */
	public static function get_name(): string {
		return __( 'Which plugin delays time-to-first-byte most?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic description.
	 */
	public static function get_description(): string {
		return __( 'Which plugin delays time-to-first-byte most? Part of Performance Attribution analysis.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 *
	 * @since  1.2601.2148
	 * @return string Category identifier.
	 */
	public static function get_category(): string {
		return 'performance_attribution';
	}

	/**
	 * Run the diagnostic test (legacy method)
	 *
	 * @since  1.2601.2148
	 * @return array Finding data or empty if no issue.
	 */
	public static function run(): array {
		$finding = self::check();
		return is_array( $finding ) ? $finding : array();
	}

	/**
	 * Get threat level for this finding (0-100)
	 *
	 * @since  1.2601.2148
	 * @return int Threat level score.
	 */
	public static function get_threat_level(): int {
		return 49;
	}

	/**
	 * Get KB article URL
	 *
	 * @since  1.2601.2148
	 * @return string Knowledge base article URL.
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/plugin-ttfb-impact/';
	}

	/**
	 * Get training video URL
	 *
	 * @since  1.2601.2148
	 * @return string Training video URL.
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/plugin-ttfb-impact/';
	}

	/**
	 * Run the diagnostic check
	 *
	 * Checks for plugins that significantly impact Time To First Byte.
	 * Data is expected to be collected by performance monitoring utilities
	 * and stored in the 'wpshadow_plugin_ttfb_impact' transient.
	 *
	 * Expected transient data structure:
	 * <code>
	 * array(
	 *     'plugin-slug' => array(
	 *         'name'    => 'Plugin Name',
	 *         'ttfb_ms' => 150,
	 *     ),
	 * )
	 * </code>
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check(): ?array {
		// Get plugin TTFB impact data from transient.
		// Expected structure: [ plugin-slug => [ name, ttfb_ms ] ]
		$plugin_data = get_transient( 'wpshadow_plugin_ttfb_impact' );

		// If no data available, cannot determine impact.
		if ( ! is_array( $plugin_data ) || empty( $plugin_data ) ) {
			return null;
		}

		// Find the plugin with the highest TTFB impact.
		$slowest_plugin = null;
		$max_impact_ms  = 0;

		foreach ( $plugin_data as $plugin_slug => $data ) {
			if ( ! is_array( $data ) || ! isset( $data['ttfb_ms'] ) ) {
				continue;
			}

			$ttfb_ms = (int) $data['ttfb_ms'];

			if ( $ttfb_ms > $max_impact_ms ) {
				$max_impact_ms  = $ttfb_ms;
				$slowest_plugin = array(
					'slug'    => $plugin_slug,
					'name'    => $data['name'] ?? $plugin_slug,
					'ttfb_ms' => $ttfb_ms,
				);
			}
		}

		// No plugin exceeds threshold.
		if ( null === $slowest_plugin || $max_impact_ms < self::THRESHOLD_MEDIUM ) {
			return null;
		}

		// Determine severity based on impact.
		if ( $max_impact_ms >= self::THRESHOLD_HIGH ) {
			$severity     = 'high';
			$threat_level = 70;
		} else {
			$severity     = 'medium';
			$threat_level = 49;
		}

		// Build user-friendly description.
		$description = sprintf(
			/* translators: 1: plugin name, 2: TTFB delay in milliseconds */
			__( 'The plugin "%1$s" adds %2$dms to your site\'s Time To First Byte (TTFB). This delays when visitors first see your content. Consider optimizing this plugin, finding a lighter alternative, or contacting the plugin developer about performance.', 'wpshadow' ),
			esc_html( $slowest_plugin['name'] ),
			$max_impact_ms
		);

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => $description,
			'severity'      => $severity,
			'threat_level'  => $threat_level,
			'category'      => self::get_category(),
			'auto_fixable'  => false,
			'kb_link'       => self::get_kb_article(),
			'training_link' => self::get_training_video(),
			'data'          => array(
				'plugin_slug' => $slowest_plugin['slug'],
				'plugin_name' => $slowest_plugin['name'],
				'ttfb_ms'     => $slowest_plugin['ttfb_ms'],
			),
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Plugin TTFB Impact
	 * Slug: plugin-ttfb-impact
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Identifies plugins that significantly delay Time To First Byte (TTFB)
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     Test result.
	 *
	 *     @type bool   $passed  Whether the test passed.
	 *     @type string $message Human-readable test result message.
	 * }
	 */
	public static function test_live_plugin_ttfb_impact(): array {
		// Get the diagnostic result.
		$result = self::check();

		// Get current transient data for context.
		$plugin_data = get_transient( 'wpshadow_plugin_ttfb_impact' );

		// If no data is available, the check should return null.
		if ( ! is_array( $plugin_data ) || empty( $plugin_data ) ) {
			if ( null === $result ) {
				return array(
					'passed'  => true,
					'message' => __( 'Test passed: No plugin TTFB data available, check correctly returns null', 'wpshadow' ),
				);
			}

			return array(
				'passed'  => false,
				'message' => __( 'Test failed: No plugin TTFB data available but check returned non-null result', 'wpshadow' ),
			);
		}

		// Find highest impact plugin from transient.
		$max_impact_ms = 0;
		foreach ( $plugin_data as $data ) {
			if ( is_array( $data ) && isset( $data['ttfb_ms'] ) ) {
				$max_impact_ms = max( $max_impact_ms, (int) $data['ttfb_ms'] );
			}
		}

		// If max impact is below threshold, check should return null.
		if ( $max_impact_ms < self::THRESHOLD_MEDIUM ) {
			if ( null === $result ) {
				return array(
					'passed'  => true,
					'message' => sprintf(
						/* translators: %d: TTFB impact in milliseconds */
						__( 'Test passed: Highest plugin TTFB impact (%dms) is below threshold, check correctly returns null', 'wpshadow' ),
						$max_impact_ms
					),
				);
			}

			return array(
				'passed'  => false,
				'message' => sprintf(
					/* translators: %d: TTFB impact in milliseconds */
					__( 'Test failed: Highest plugin TTFB impact (%dms) is below threshold but check returned non-null result', 'wpshadow' ),
					$max_impact_ms
				),
			);
		}

		// If max impact is above threshold, check should return an array.
		if ( is_array( $result ) ) {
			// Verify structure.
			$required_keys = array( 'id', 'title', 'description', 'severity', 'threat_level' );
			$missing_keys  = array_diff( $required_keys, array_keys( $result ) );

			if ( ! empty( $missing_keys ) ) {
				return array(
					'passed'  => false,
					'message' => sprintf(
						/* translators: %s: comma-separated list of missing keys */
						__( 'Test failed: Result array missing required keys: %s', 'wpshadow' ),
						implode( ', ', $missing_keys )
					),
				);
			}

			return array(
				'passed'  => true,
				'message' => sprintf(
					/* translators: %d: TTFB impact in milliseconds */
					__( 'Test passed: Highest plugin TTFB impact (%dms) exceeds threshold, check correctly returns finding array', 'wpshadow' ),
					$max_impact_ms
				),
			);
		}

		return array(
			'passed'  => false,
			'message' => sprintf(
				/* translators: %d: TTFB impact in milliseconds */
				__( 'Test failed: Highest plugin TTFB impact (%dms) exceeds threshold but check returned null', 'wpshadow' ),
				$max_impact_ms
			),
		);
	}
}
