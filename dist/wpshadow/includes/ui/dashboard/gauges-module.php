<?php

/**
 * WPShadow Health Gauges Dashboard Module
 *
 * Handles health gauge rendering for:
 * - Overall site health score
 * - Category-specific health gauges
 * - Asset enqueuing for gauge styles
 * - Health calculation and display
 *
 * @package WPShadow
 * @subpackage Dashboard
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue gauges and dashboard assets
 */
function wpshadow_enqueue_gauges_assets( string $hook ): void {
	if ( strpos( $hook, 'wpshadow' ) === false ) {
		return;
	}

	// Enqueue gauges CSS for health dashboard (#563)
	wp_enqueue_style(
		'wpshadow-gauges',
		WPSHADOW_URL . 'assets/css/gauges.css',
		array( 'wpshadow-design-system' ), // Depends on design system for CSS variables
		WPSHADOW_VERSION
	);

	wp_enqueue_style(
		'wpshadow-safety-warnings',
		WPSHADOW_URL . 'assets/css/utilities-consolidated.css',
		array(),
		WPSHADOW_VERSION
	);

	// Real-time dashboard updates and fullscreen mode (new feature)
	wp_enqueue_style(
		'wpshadow-dashboard-fullscreen',
		WPSHADOW_URL . 'assets/css/wpshadow-dashboard-fullscreen.css',
		array(),
		WPSHADOW_VERSION
	);

	wp_enqueue_script(
		'wpshadow-dashboard-realtime',
		WPSHADOW_URL . 'assets/js/wpshadow-dashboard-realtime.js',
		array( 'jquery', 'heartbeat' ),
		file_exists( WPSHADOW_PATH . 'assets/js/wpshadow-dashboard-realtime.js' ) ? (string) filemtime( WPSHADOW_PATH . 'assets/js/wpshadow-dashboard-realtime.js' ) : WPSHADOW_VERSION,
		false // Load in header so inline scripts can use jQuery
	);

	// Ensure WordPress heartbeat API is active on dashboard pages.
	wp_enqueue_script( 'heartbeat' );

	// Localize dashboard script with nonce
	wp_localize_script(
		'wpshadow-dashboard-realtime',
		'wpshadowDashboardData',
		array(
			'dashboard_nonce'  => wp_create_nonce( 'wpshadow_dashboard_nonce' ),
			'first_scan_nonce' => wp_create_nonce( 'wpshadow_first_scan_nonce' ),
			'scan_nonce'       => wp_create_nonce( 'wpshadow_scan_nonce' ),
			'tests_run_label'  => __( 'Tests run: %1$d/%2$d', 'wpshadow' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'wpshadow_enqueue_gauges_assets' );

/**
 * Get site health status
 *
 * Calculate overall site health score from all category findings
 *
 * @return array Health status with score, color, message
 */
function wpshadow_get_health_status(): array {
	$findings = \wpshadow_get_site_findings();

	// Check if diagnostics have ever been run
	$last_scan = get_option( 'wpshadow_last_quick_scan', 0 );
	$never_run = empty( $last_scan );

	// Calculate summed score across all non-overall gauges shown on the dashboard.
	$category_meta = \wpshadow_get_category_metadata();
	$total_score   = 0;
	$gauge_count   = 0;

	foreach ( $category_meta as $cat_key => $meta ) {
		if ( 'overall' === $cat_key ) {
			continue;
		}

		if ( 'wordpress-health' === $cat_key ) {
			$wp_health_score = (int) ( wpshadow_get_wordpress_health()['score'] ?? 0 );
			$total_score    += max( 0, min( 100, $wp_health_score ) );
			++$gauge_count;
			continue;
		}

		$cat_findings = array_filter(
			$findings,
			function ( $finding ) use ( $cat_key ) {
				return isset( $finding['category'] ) && $finding['category'] === $cat_key;
			}
		);

		$total        = count( $cat_findings );
		$threat_total = 0;

		foreach ( $cat_findings as $finding ) {
			$threat_total += isset( $finding['threat_level'] ) ? (int) $finding['threat_level'] : 50;
		}

		$gauge_percent = $total > 0 ? min( 100, $threat_total / $total ) : 0;
		$gauge_percent = 100 - $gauge_percent; // Invert: higher is better.

		// Keep overall in sync with the small gauge UI prior to first scan completion.
		if ( $never_run ) {
			$gauge_percent = 0;
		}

		$total_score += $gauge_percent;
		++$gauge_count;
	}

	// Sum all category percents (0–1000 scale: 10 categories × 100).
	$weighted_score = (int) round( $total_score );
	$weighted_score = max( 0, min( 1000, $weighted_score ) );

	if ( $weighted_score >= 800 ) {
		$status  = __( 'Good', 'wpshadow' );
		$color   = '#2e7d32';
		$message = sprintf(
			/* translators: %d: health score out of 1000 */
			__( 'Your site is in good health: %d/1000.', 'wpshadow' ),
			$weighted_score
		);
	} elseif ( $weighted_score >= 600 ) {
		$status  = __( 'Fair', 'wpshadow' );
		$color   = '#f57c00';
		$message = sprintf(
			/* translators: %d: health score out of 1000 */
			__( 'Your site needs attention: %d/1000.', 'wpshadow' ),
			$weighted_score
		);
	} else {
		$status  = __( 'Poor', 'wpshadow' );
		$color   = '#c62828';
		$message = sprintf(
			/* translators: %d: health score out of 1000 */
			__( 'Your site requires immediate attention: %d/1000.', 'wpshadow' ),
			$weighted_score
		);
	}

	return array(
		'score'   => $weighted_score,
		'status'  => $status,
		'color'   => $color,
		'message' => $message,
	);
}

/**
 * Get WordPress native Site Health score
 *
 * Retrieves the WordPress Site Health status from WordPress's health check transient
 *
 * @return array WordPress health status
 */
function wpshadow_get_wordpress_health(): array {
	// Get WordPress site health check results from transient
	// WordPress stores: {"good": X, "recommended": Y, "critical": Z}
	$health_check = get_transient( 'health-check-site-status-result' );
	
	if ( false === $health_check ) {
		// Transient not set - health checks haven't been run
		return array(
			'score'   => 0,
			'status'  => __( 'Not Checked', 'wpshadow' ),
			'color'   => '#999999',
			'message' => __( 'WordPress Site Health hasn\'t been checked yet.', 'wpshadow' ),
		);
	}

	if ( ! is_array( $health_check ) ) {
		// Try to parse if it's JSON string
		$parsed = json_decode( $health_check, true );
		if ( is_array( $parsed ) ) {
			$health_check = $parsed;
		} else {
			return array(
				'score'   => 0,
				'status'  => __( 'Unknown', 'wpshadow' ),
				'color'   => '#999999',
				'message' => __( 'Unable to interpret WordPress Site Health data.', 'wpshadow' ),
			);
		}
	}

	// Calculate score from health check results
	// Formula: (good * 100 + recommended * 50) / (good + recommended + critical) 
	// This gives us a percentage between 0-100
	$good = isset( $health_check['good'] ) ? (int) $health_check['good'] : 0;
	$recommended = isset( $health_check['recommended'] ) ? (int) $health_check['recommended'] : 0;
	$critical = isset( $health_check['critical'] ) ? (int) $health_check['critical'] : 0;

	$total = $good + $recommended + $critical;

	if ( 0 === $total ) {
		// No health checks available
		return array(
			'score'   => 0,
			'status'  => __( 'No Data', 'wpshadow' ),
			'color'   => '#999999',
			'message' => __( 'WordPress Site Health has no check data.', 'wpshadow' ),
		);
	}

	// Calculate percentage: Weight good as 100%, recommended as 50%, critical as 0%
	$percentage = (int) ( ( ( $good * 100 ) + ( $recommended * 50 ) ) / $total );
	$percentage = max( 0, min( 100, $percentage ) );

	// Determine status and color based on critical issues
	if ( $critical > 0 ) {
		$status = __( 'Critical', 'wpshadow' );
		$color = '#c62828'; // Red
	} elseif ( $recommended > 0 ) {
		$status = __( 'Recommended', 'wpshadow' );
		$color = '#f57c00'; // Orange
	} else {
		$status = __( 'Good', 'wpshadow' );
		$color = '#2e7d32'; // Green
	}

	return array(
		'score'  => $percentage,
		'status' => $status,
		'color'  => $color,
		'message' => sprintf(
			/* translators: %d: WordPress health percentage */
			__( 'WordPress reports %d%% health.', 'wpshadow' ),
			$percentage
		),
	);
}

/**
 * Get per-category test coverage counts for dashboard gauges.
 *
 * Counts associated diagnostics by category and reports how many actually ran
 * during the current dashboard freshness window.
 *
 * @param array $category_meta Dashboard category metadata.
 * @param bool  $never_run     Whether diagnostics have never been run.
 * @return array<string, array{run: int, total: int}> Category counts.
 */
function wpshadow_get_gauge_test_counts( array $category_meta, bool $never_run ): array {
	$counts = array();

	foreach ( $category_meta as $cat_key => $meta ) {
		$counts[ $cat_key ] = array(
			'run'     => 0,
			'passed'  => 0,
			'failed'  => 0,
			'unknown' => 0,
			'total'   => 0,
		);
	}

	$diagnostic_file_map = class_exists( '\\WPShadow\\Diagnostics\\Diagnostic_Registry' )
		? \WPShadow\Diagnostics\Diagnostic_Registry::get_diagnostic_file_map()
		: array();

	$disabled = get_option( 'wpshadow_disabled_diagnostic_classes', array() );
	if ( ! is_array( $disabled ) ) {
		$disabled = array();
	}

	// Load all persisted states once for the raw-state fallback (mirrors Diagnostic Status table logic).
	$all_raw_states = function_exists( 'wpshadow_get_diagnostic_test_states' )
		? wpshadow_get_diagnostic_test_states()
		: array();

	$now = time();
	foreach ( $diagnostic_file_map as $class_name => $diagnostic_data ) {
		if ( ! is_string( $class_name ) || '' === $class_name ) {
			continue;
		}

		$qualified_class = 0 === strpos( $class_name, 'WPShadow\\Diagnostics\\' )
			? $class_name
			: 'WPShadow\\Diagnostics\\' . $class_name;

		$is_disabled = in_array( $qualified_class, $disabled, true ) || in_array( $class_name, $disabled, true );
		if ( $is_disabled ) {
			continue;
		}

		$category = sanitize_key( (string) ( $diagnostic_data['family'] ?? '' ) );
		if ( ! isset( $counts[ $category ] ) ) {
			continue;
		}

		++$counts[ $category ]['total'];

		$state = function_exists( 'wpshadow_get_valid_diagnostic_test_state' )
			? wpshadow_get_valid_diagnostic_test_state( $qualified_class, $now )
			: null;

		// Fallback: use persisted raw state when the valid-state check returns null
		// (e.g. transient expired). This matches the same fallback used in the
		// Diagnostic Status table so both surfaces show consistent counts.
		if ( ! is_array( $state ) && isset( $all_raw_states[ $qualified_class ] ) && is_array( $all_raw_states[ $qualified_class ] ) ) {
			$raw_status = (string) ( $all_raw_states[ $qualified_class ]['status'] ?? '' );
			if ( 'passed' === $raw_status || 'failed' === $raw_status ) {
				$state = $all_raw_states[ $qualified_class ];
			}
		}

		if ( ! is_array( $state ) ) {
			++$counts[ $category ]['unknown'];
			continue;
		}

		$status = (string) ( $state['status'] ?? 'unknown' );
		if ( 'passed' === $status ) {
			++$counts[ $category ]['passed'];
			++$counts[ $category ]['run'];
		} elseif ( 'failed' === $status ) {
			++$counts[ $category ]['failed'];
			++$counts[ $category ]['run'];
		} else {
			++$counts[ $category ]['unknown'];
		}
	}

	$wp_health_total = 0;
	if ( class_exists( '\\WP_Site_Health' ) || file_exists( ABSPATH . 'wp-admin/includes/class-wp-site-health.php' ) ) {
		if ( ! class_exists( '\\WP_Site_Health' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-site-health.php';
		}

		$wp_tests = \WP_Site_Health::get_tests();
		foreach ( array( 'direct', 'async' ) as $test_type ) {
			if ( empty( $wp_tests[ $test_type ] ) || ! is_array( $wp_tests[ $test_type ] ) ) {
				continue;
			}

			foreach ( $wp_tests[ $test_type ] as $test ) {
				$test_slug = (string) ( $test['test'] ?? '' );
				if ( '' !== $test_slug && false !== strpos( $test_slug, 'wpshadow' ) ) {
					continue;
				}
				++$wp_health_total;
			}
		}
	}

	$wp_health_run = 0;
	$wp_health_raw = get_transient( 'health-check-site-status-result' );
	if ( is_string( $wp_health_raw ) ) {
		$decoded = json_decode( $wp_health_raw, true );
		if ( is_array( $decoded ) ) {
			$wp_health_raw = $decoded;
		}
	}

	if ( is_array( $wp_health_raw ) ) {
		$wp_health_run = (int) ( $wp_health_raw['good'] ?? 0 ) + (int) ( $wp_health_raw['recommended'] ?? 0 ) + (int) ( $wp_health_raw['critical'] ?? 0 );
	}

	if ( isset( $counts['wordpress-health'] ) ) {
		$wp_known = 0;
		$wp_good  = 0;

		if ( is_array( $wp_health_raw ) ) {
			$wp_good        = (int) ( $wp_health_raw['good'] ?? 0 );
			$wp_recommended = (int) ( $wp_health_raw['recommended'] ?? 0 );
			$wp_critical    = (int) ( $wp_health_raw['critical'] ?? 0 );
			$wp_known       = $wp_good + $wp_recommended + $wp_critical;
		}

		$counts['wordpress-health']['total']   = $wp_health_total;
		$counts['wordpress-health']['passed']  = min( $wp_good, $wp_health_total );
		$counts['wordpress-health']['failed']  = min( max( 0, $wp_known - $wp_good ), $wp_health_total );
		$counts['wordpress-health']['run']     = min( $wp_known, $wp_health_total );
		$counts['wordpress-health']['unknown'] = max( 0, $wp_health_total - $counts['wordpress-health']['run'] );
	}

	// Calculate overall test counts from category aggregates.
	$overall_run     = 0;
	$overall_passed  = 0;
	$overall_failed  = 0;
	$overall_unknown = 0;
	$overall_total   = 0;
	foreach ( $counts as $cat_key => $cat_counts ) {
		if ( 'overall' === $cat_key ) {
			continue;
		}

		$overall_run     += (int) ( $cat_counts['run'] ?? 0 );
		$overall_passed  += (int) ( $cat_counts['passed'] ?? 0 );
		$overall_failed  += (int) ( $cat_counts['failed'] ?? 0 );
		$overall_unknown += (int) ( $cat_counts['unknown'] ?? 0 );
		$overall_total   += (int) ( $cat_counts['total'] ?? 0 );
	}

	$counts['overall'] = array(
		'run'     => $overall_run,
		'passed'  => $overall_passed,
		'failed'  => $overall_failed,
		'unknown' => $overall_unknown,
		'total'   => $overall_total,
	);

	return $counts;
}

/**
 * Render health gauges section on dashboard
 *
 * Called via wpshadow_dashboard_gauges hook
 * Renders overall health gauge and category-specific gauges
 * Always shows gauges, even when no findings exist (Issue #1672)
 *
 * @param string $category_filter Optional category to filter by (Issue #564)
 */
function wpshadow_render_health_gauges( string $category_filter = '' ): void {
	$is_drilldown = ! empty( $category_filter );

	// Get findings (empty array if none)
	$findings = \wpshadow_get_site_findings();

	// Check if diagnostics have ever been run
	$last_scan = get_option( 'wpshadow_last_quick_scan', 0 );
	$never_run = empty( $last_scan );

	// Group findings by category
	$category_meta = \wpshadow_get_category_metadata();

		// Filter categories if drill-down active
	if ( $is_drilldown ) {
		// Show only the selected category gauge prominently
		if ( isset( $category_meta[ $category_filter ] ) ) {
			$category_meta = array( $category_filter => $category_meta[ $category_filter ] );
		} else {
			return; // Invalid category
		}
	}

	$test_counts = wpshadow_get_gauge_test_counts( $category_meta, $never_run );

	$findings_by_category = array();
	foreach ( $category_meta as $cat_key => $meta ) {
		$findings_by_category[ $cat_key ] = array_filter(
			$findings,
			function ( $f ) use ( $cat_key ) {
				return isset( $f['category'] ) && $f['category'] === $cat_key;
			}
		);
	}

	// Calculate test pass rates from persisted per-test states.
	$overall_total_tests   = (int) ( $test_counts['overall']['total'] ?? 0 );
	$overall_tests_passed  = (int) ( $test_counts['overall']['passed'] ?? 0 );
	$overall_tests_unknown = (int) ( $test_counts['overall']['unknown'] ?? 0 );
	$overall_pass_rate     = $overall_total_tests > 0 ? (int) ( ( $overall_tests_passed / $overall_total_tests ) * 100 ) : 0;

	// Get pass rate color utility function
	$get_pass_rate_color = function ( $pass_rate ) {
		if ( $pass_rate >= 80 ) {
			return '#2e7d32'; // Green - Good pass rate
		}
		if ( $pass_rate >= 60 ) {
			return '#f57c00'; // Orange - Fair pass rate
		}
		return '#f44336'; // Red - Poor pass rate
	};
	
	$overall_color = $get_pass_rate_color( $overall_pass_rate );
	$overall_report = function_exists( 'wpshadow_get_dashboard_gauge_report_for_category' )
		? wpshadow_get_dashboard_gauge_report_for_category( 'overall' )
		: null;
	$overall_report_url = is_array( $overall_report ) && ! empty( $overall_report['report'] )
		? add_query_arg(
			array(
				'page'   => 'wpshadow-reports',
				'report' => sanitize_key( (string) $overall_report['report'] ),
			),
			admin_url( 'admin.php' )
		)
		: admin_url( 'admin.php?page=wpshadow-reports' );
	$wordpress_site_health_url = admin_url( 'site-health.php' );
	?>
	<div class="wps-dashboard-gauges wps-gap-6 wps-mb-8">
		<!-- Left: Large Overall Health Gauge + Scan Buttons -->
		<div class="wps-health-gauge-main">
			<a href="<?php echo esc_url( $overall_report_url ); ?>" class="wps-health-gauge-card" style="display:block; color:inherit; text-decoration:none; border-color: <?php echo esc_attr( $overall_color ); ?>;" aria-label="<?php esc_attr_e( 'Open the Overall Health report', 'wpshadow' ); ?>">
				<h3 class="wps-health-gauge-title"><?php esc_html_e( 'Overall Site Health', 'wpshadow' ); ?></h3>

				<svg id="wpshadow-overall-gauge" width="200" height="200" viewBox="0 0 200 200" class="wps-health-gauge-svg" aria-labelledby="overall-health-title" role="img">
					<title id="overall-health-title">
						<?php
						echo esc_html(
							sprintf(
								/* translators: 1: tests passed, 2: total tests, 3: pass rate percentage */
								__( 'Overall tests passed: %1$d/%2$d (%3$d%%)', 'wpshadow' ),
								$overall_tests_passed,
								$overall_total_tests,
								$overall_pass_rate
							)
						);
						?>
					</title>
					<!-- Outer decorative circle -->
					<circle cx="100" cy="100" r="95" fill="none" stroke="<?php echo esc_attr( $overall_color ); ?>" stroke-width="2" opacity="0.2" />
					<!-- Gauge background -->
					<circle cx="100" cy="100" r="85" fill="none" stroke="#e0e0e0" stroke-width="16" />
					<!-- Gauge progress -->
					<circle cx="100" cy="100" r="85" fill="none" stroke="<?php echo esc_attr( $overall_color ); ?>" stroke-width="16"
						stroke-dasharray="<?php echo (int) ( ( $overall_pass_rate / 100 ) * 534 ); ?> 534"
						stroke-linecap="round" transform="rotate(-90 100 100)"
						class="wps-gauge-progress" />
					<!-- Center text -->
					<text x="100" y="97" text-anchor="middle" font-size="40" font-weight="bold" fill="<?php echo esc_attr( $overall_color ); ?>"><?php echo (int) $overall_tests_passed; ?></text>
					<text x="100" y="118" text-anchor="middle" font-size="16" fill="#999">/<?php echo (int) $overall_total_tests; ?></text>
					<text x="100" y="138" text-anchor="middle" font-size="14" fill="#666"><?php echo esc_html( sprintf( __( '%d%% Pass', 'wpshadow' ), (int) $overall_pass_rate ) ); ?></text>
				</svg>

				<p class="wps-health-gauge-message">
					<?php
					if ( $overall_tests_unknown > 0 ) {
						echo esc_html(
							sprintf(
								/* translators: 1: unknown test count */
								__( '%1$d tests are still unknown until they run.', 'wpshadow' ),
								$overall_tests_unknown
							)
						);
					} else {
						echo esc_html(
							sprintf(
								/* translators: 1: pass rate percentage */
								__( '%1$d%% of tests passed', 'wpshadow' ),
								$overall_pass_rate
							)
						);
					}
					?>
				</p>

				<div class="wps-health-gauge-test-count" data-test-category="overall">
					<?php
					if ( $overall_tests_unknown > 0 ) {
						echo esc_html(
							sprintf(
								/* translators: 1: tests passed, 2: total tests, 3: unknown tests */
								__( 'Passed: %1$d/%2$d (Unknown: %3$d)', 'wpshadow' ),
								$overall_tests_passed,
								$overall_total_tests,
								$overall_tests_unknown
							)
						);
					} else {
						echo esc_html(
							sprintf(
								/* translators: 1: tests passed, 2: total tests */
								__( 'Passed: %1$d/%2$d', 'wpshadow' ),
								$overall_tests_passed,
								$overall_total_tests
							)
						);
					}
					?>
				</div>
			</a>

			<!-- Fullscreen Button Only -->
			<div class="wps-health-gauge-actions">
				<button id="wpshadow-fullscreen-toggle" class="button wps-btn-scan" title="<?php esc_attr_e( 'View dashboard in fullscreen mode (great for office displays)', 'wpshadow' ); ?>" aria-label="<?php esc_attr_e( 'Toggle fullscreen mode', 'wpshadow' ); ?>">
					<span class="dashicons dashicons-fullscreen-alt" aria-hidden="true"></span>
					<?php esc_html_e( 'Full Screen', 'wpshadow' ); ?>
				</button>
			</div>
		</div>

		<!-- Right: Category Gauges in a responsive grid -->
		<div class="wps-health-gauge-categories">
			<div class="wps-health-gauge-grid">
				<?php
				foreach ( $category_meta as $cat_key => $meta ) :				// Skip the overall category (shown as large gauge on left)
				if ( 'overall' === $cat_key ) {
					continue;
				}
									// Special handling for WordPress Health category
									if ( 'wordpress-health' === $cat_key ) {
										// Calculate WordPress Health pass rate from test_counts (same source as label).
										$wp_test_total  = (int) ( $test_counts['wordpress-health']['total'] ?? 0 );
										$wp_test_passed = (int) ( $test_counts['wordpress-health']['passed'] ?? 0 );
										$gauge_percent  = $wp_test_total > 0 ? (int) ( ( $wp_test_passed / $wp_test_total ) * 100 ) : 0;

										// Derive status from the same pass-rate metric shown on the gauge.
										if ( $gauge_percent >= 80 ) {
											$status_text = __( 'Excellent', 'wpshadow' );
											$status_icon = '✓';
										} elseif ( $gauge_percent >= 60 ) {
											$status_text = __( 'Good', 'wpshadow' );
											$status_icon = '✓';
										} elseif ( $gauge_percent >= 40 ) {
											$status_text = __( 'Fair', 'wpshadow' );
											$status_icon = '◐';
										} else {
											$status_text = __( 'Needs Work', 'wpshadow' );
											$status_icon = '✕';
										}
					
										// Determine status color based on pass rate
										if ( $gauge_percent >= 80 ) {
											$status_color = '#10b981'; // Green
											$gauge_color = '#2e7d32';
										} elseif ( $gauge_percent >= 60 ) {
											$status_color = '#f59e0b'; // Orange
											$gauge_color = '#f57c00';
										} else {
											$status_color = '#ef4444'; // Red
											$gauge_color = '#c62828';
										}
						?>
						<a href="<?php echo esc_url( $wordpress_site_health_url ); ?>" class="wps-category-gauge" data-category="wordpress-health" aria-label="<?php esc_attr_e( 'Open the WordPress Site Health tool', 'wpshadow' ); ?>">
							<div class="wps-category-gauge-icon">
								<svg width="70" height="70" viewBox="0 0 100 100" aria-hidden="true">
									<!-- Gauge background -->
									<circle cx="50" cy="50" r="40" fill="none" stroke="#e0e0e0" stroke-width="8" />
									<!-- Gauge progress -->
									<circle cx="50" cy="50" r="40" fill="none" stroke="<?php echo esc_attr( $gauge_color ); ?>" stroke-width="8"
										class="wps-gauge-progress"
										stroke-dasharray="<?php echo (int) ( $gauge_percent / 100 * 251 ); ?> 251"
										stroke-linecap="round" transform="rotate(-90 50 50)" />
									<!-- Percentage text -->
									<text x="50" y="55" text-anchor="middle" font-size="18" font-weight="bold" fill="#333"><?php echo (int) $gauge_percent; ?>%</text>
								</svg>
							</div>

							<div class="wps-category-gauge-content">
								<!-- Title -->
								<h4 class="wps-category-gauge-title"><?php echo esc_html( isset( $meta['label'] ) ? $meta['label'] : ucfirst( $cat_key ) ); ?></h4>

								<!-- Status -->
								<div class="wps-category-gauge-status">
									<span class="wps-category-gauge-status-text">
										<span aria-hidden="true"><?php echo esc_html( $status_icon ); ?></span>
										<?php echo esc_html( $status_text ); ?>
									</span>
									<div class="wps-category-gauge-count" data-test-category="<?php echo esc_attr( $cat_key ); ?>">
										<?php
										echo esc_html(
											sprintf(
												/* translators: 1: passed tests, 2: total tests */
												__( 'Passed: %1$d/%2$d', 'wpshadow' ),
												$wp_test_passed,
												$wp_test_total
											)
										);
										?>
									</div>
								</div>
							</div>
						</a>
						<?php
						continue;
					}
					
					// Calculate category test pass rate from persisted states.
					$cat_test_total   = (int) ( $test_counts[ $cat_key ]['total'] ?? 0 );
					$cat_test_passed  = (int) ( $test_counts[ $cat_key ]['passed'] ?? 0 );
					$cat_test_unknown = (int) ( $test_counts[ $cat_key ]['unknown'] ?? 0 );
					$cat_pass_rate   = $cat_test_total > 0 ? (int) ( ( $cat_test_passed / $cat_test_total ) * 100 ) : 0;
				
					// Show 0% until diagnostics have been run
					if ( $never_run ) {
						$cat_pass_rate   = 0;
						$cat_test_passed = 0;
					}

					// Set status and colors based on pass rate
					if ( $cat_pass_rate >= 80 ) {
						$status_text  = __( 'Excellent', 'wpshadow' );
						$status_icon  = '✓';
						$status_color = '#10b981';
						$gauge_color  = '#2e7d32';
					} elseif ( $cat_pass_rate >= 60 ) {
						$status_text  = __( 'Good', 'wpshadow' );
						$status_icon  = '✓';
						$status_color = '#10b981';
						$gauge_color  = '#2e7d32';
					} elseif ( $cat_pass_rate >= 40 ) {
						$status_text  = __( 'Fair', 'wpshadow' );
						$status_icon  = '◐';
						$status_color = '#f59e0b';
						$gauge_color  = '#f57c00';
					} else {
						$status_text  = __( 'Needs Work', 'wpshadow' );
						$status_icon  = '✕';
						$status_color = '#ef4444';
						$gauge_color  = '#c62828';
					}
				
					$gauge_percent = $cat_pass_rate;
					$cat_test_run   = (int) ( $test_counts[ $cat_key ]['run'] ?? 0 );
					$cat_test_total = (int) ( $test_counts[ $cat_key ]['total'] ?? 0 );
					?>
					<?php
					$category_report = function_exists( 'wpshadow_get_dashboard_gauge_report_for_category' )
						? wpshadow_get_dashboard_gauge_report_for_category( $cat_key )
						: null;
					$category_report_url = is_array( $category_report ) && ! empty( $category_report['report'] )
						? add_query_arg(
							array(
								'page'   => 'wpshadow-reports',
								'report' => sanitize_key( (string) $category_report['report'] ),
							),
							admin_url( 'admin.php' )
						)
						: admin_url( 'admin.php?page=wpshadow-reports' );
					?>
					<a href="<?php echo esc_url( $category_report_url ); ?>" 
						class="wps-category-gauge" 
						data-category="<?php echo esc_attr( $cat_key ); ?>"
						aria-label="
						<?php
						echo esc_attr(
							sprintf(
								/* translators: 1: category name, 2: pass rate percentage, 3: tests passed, 4: total tests */
								__( '%1$s: %2$d%% pass rate (%3$d/%4$d tests). Click to open the detailed report', 'wpshadow' ),
								isset( $meta['label'] ) ? $meta['label'] : ucfirst( $cat_key ),
								$cat_pass_rate,
								$cat_test_passed,
								$cat_test_total
							)
						);
						?>
						">
						<div class="wps-category-gauge-icon">
							<svg width="70" height="70" viewBox="0 0 100 100" aria-hidden="true">
								<!-- Gauge background -->
								<circle cx="50" cy="50" r="40" fill="none" stroke="#e0e0e0" stroke-width="8" />
								<!-- Gauge progress -->
								<circle cx="50" cy="50" r="40" fill="none" stroke="<?php echo esc_attr( $gauge_color ); ?>" stroke-width="8"
									class="wps-gauge-progress"
									stroke-dasharray="<?php echo (int) ( $gauge_percent / 100 * 251 ); ?> 251"
									stroke-linecap="round" transform="rotate(-90 50 50)" />
								<!-- Percentage text -->
							<text x="50" y="55" text-anchor="middle" font-size="18" font-weight="bold" fill="#333"><?php echo (int) $gauge_percent; ?>%</text>
							</svg>
						</div>

						<div class="wps-category-gauge-content">
							<!-- Title -->
						<h4 class="wps-category-gauge-title"><?php echo esc_html( isset( $meta['label'] ) ? $meta['label'] : ucfirst( $cat_key ) ); ?></h4>

							<!-- Status -->
							<div class="wps-category-gauge-status">
							<span class="wps-category-gauge-status-text" data-status="<?php echo esc_attr( $status_text ); ?>">
									<span aria-hidden="true"><?php echo esc_html( $status_icon ); ?></span>
									<?php echo esc_html( $status_text ); ?>
								</span>
								<div class="wps-category-gauge-count" data-test-category="<?php echo esc_attr( $cat_key ); ?>">
									<?php
									if ( $cat_test_unknown > 0 ) {
										echo esc_html(
											sprintf(
												/* translators: 1: passed tests, 2: total associated tests, 3: unknown tests */
												__( 'Passed: %1$d/%2$d (Unknown: %3$d)', 'wpshadow' ),
												$cat_test_passed,
												$cat_test_total,
												$cat_test_unknown
											)
										);
									} else {
										echo esc_html(
											sprintf(
												/* translators: 1: passed tests, 2: total associated tests */
												__( 'Passed: %1$d/%2$d', 'wpshadow' ),
												$cat_test_passed,
												$cat_test_total
											)
										);
									}
									?>
								</div>
							</div>
						</div>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<?php
}

// Hook gauges rendering into dashboard
add_action( 'wpshadow_dashboard_gauges', 'wpshadow_render_health_gauges' );
