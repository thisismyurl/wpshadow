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
		array( 'jquery' ),
		WPSHADOW_VERSION,
		false // Load in header so inline scripts can use jQuery
	);

	// Localize dashboard script with nonce
	wp_localize_script(
		'wpshadow-dashboard-realtime',
		'wpshadowDashboardData',
		array(
			'dashboard_nonce'  => wp_create_nonce( 'wpshadow_dashboard_nonce' ),
			'first_scan_nonce' => wp_create_nonce( 'wpshadow_first_scan_nonce' ),
			'scan_nonce'       => wp_create_nonce( 'wpshadow_scan_nonce' ),
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
 * Counts associated diagnostics by category and estimates how many have run
 * based on whether a scan has been executed and whether diagnostics are disabled.
 *
 * @param array $category_meta Dashboard category metadata.
 * @param bool  $never_run     Whether diagnostics have never been run.
 * @return array<string, array{run: int, total: int}> Category counts.
 */
function wpshadow_get_gauge_test_counts( array $category_meta, bool $never_run ): array {
	$counts = array();

	foreach ( $category_meta as $cat_key => $meta ) {
		$counts[ $cat_key ] = array(
			'run'   => 0,
			'total' => 0,
		);
	}

	$disabled = get_option( 'wpshadow_disabled_diagnostic_classes', array() );
	if ( ! is_array( $disabled ) ) {
		$disabled = array();
	}

	if ( class_exists( '\\WPShadow\\Diagnostics\\Diagnostic_Registry' ) && method_exists( '\\WPShadow\\Diagnostics\\Diagnostic_Registry', 'get_diagnostic_file_map' ) ) {
		$diagnostic_file_map = \WPShadow\Diagnostics\Diagnostic_Registry::get_diagnostic_file_map();

		foreach ( $diagnostic_file_map as $class_name => $diagnostic_data ) {
			$category = sanitize_key( (string) ( $diagnostic_data['family'] ?? '' ) );
			if ( empty( $category ) || ! isset( $counts[ $category ] ) || 'overall' === $category || 'wordpress-health' === $category ) {
				continue;
			}

			++$counts[ $category ]['total'];

			$qualified_class = 0 === strpos( $class_name, 'WPShadow\\Diagnostics\\' ) ? $class_name : 'WPShadow\\Diagnostics\\' . $class_name;
			$is_disabled     = in_array( $qualified_class, $disabled, true ) || in_array( $class_name, $disabled, true );

			if ( ! $never_run && ! $is_disabled ) {
				++$counts[ $category ]['run'];
			}
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
		$counts['wordpress-health']['total'] = $wp_health_total;
		$counts['wordpress-health']['run']   = $never_run ? 0 : min( $wp_health_run, $wp_health_total );
	}

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

	// Calculate overall health from all categories
	$overall_health = \wpshadow_get_health_status();

	// Get threat gauge color utility function
	$get_threat_gauge_color = function ( $threat_level ) {
		if ( $threat_level <= 25 ) {
			return '#2e7d32'; // Green - Low threat
		}
		if ( $threat_level <= 50 ) {
			return '#f57c00'; // Orange - Medium threat
		}
		return '#f44336'; // Red - High threat
	};
	?>
	<div class="wps-dashboard-gauges wps-gap-6 wps-mb-8">
		<!-- Left: Large Overall Health Gauge + Scan Buttons -->
		<div class="wps-health-gauge-main">
			<div class="wps-health-gauge-card" style="border-color: <?php echo esc_attr( isset( $overall_health['color'] ) ? $overall_health['color'] : '#ccc' ); ?>;">
				<h3 class="wps-health-gauge-title"><?php esc_html_e( 'Overall Site Health', 'wpshadow' ); ?></h3>

				<svg id="wpshadow-overall-gauge" width="200" height="200" viewBox="0 0 200 200" class="wps-health-gauge-svg" aria-labelledby="overall-health-title" role="img">
					<title id="overall-health-title">
						<?php
						echo esc_html(
							sprintf(
								/* translators: %d: health score out of 1000 */
								__( 'Overall site health: %d/1000', 'wpshadow' ),
								isset( $overall_health['score'] ) ? (int) $overall_health['score'] : 0
							)
						);
						?>
					</title>
					<!-- Outer decorative circle -->
					<circle cx="100" cy="100" r="95" fill="none" stroke="<?php echo esc_attr( isset( $overall_health['color'] ) ? $overall_health['color'] : '#ccc' ); ?>" stroke-width="2" opacity="0.2" />
					<!-- Gauge background -->
					<circle cx="100" cy="100" r="85" fill="none" stroke="#e0e0e0" stroke-width="16" />
					<!-- Gauge progress -->
					<circle cx="100" cy="100" r="85" fill="none" stroke="<?php echo esc_attr( isset( $overall_health['color'] ) ? $overall_health['color'] : '#ccc' ); ?>" stroke-width="16"
						stroke-dasharray="<?php echo (int) ( ( isset( $overall_health['score'] ) ? $overall_health['score'] : 0 ) / 1000 * 534 ); ?> 534"
						stroke-linecap="round" transform="rotate(-90 100 100)"
						class="wps-gauge-progress" />
					<!-- Center text -->
				<text x="100" y="97" text-anchor="middle" font-size="40" font-weight="bold" fill="<?php echo esc_attr( isset( $overall_health['color'] ) ? $overall_health['color'] : '#ccc' ); ?>"><?php echo isset( $overall_health['score'] ) ? (int) $overall_health['score'] : 0; ?></text>
				<text x="100" y="118" text-anchor="middle" font-size="16" fill="#999">/1000</text>
				<text x="100" y="138" text-anchor="middle" font-size="14" fill="#666"><?php echo esc_html( isset( $overall_health['status'] ) && $overall_health['status'] ? $overall_health['status'] : 'Unknown' ); ?></text>
				</svg>

				<p class="wps-health-gauge-message"><?php echo esc_html( isset( $overall_health['message'] ) && $overall_health['message'] ? $overall_health['message'] : '' ); ?></p>
			</div>

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
						$wp_health = \wpshadow_get_wordpress_health();
						$gauge_percent = $wp_health['score'];
						$status_text = $wp_health['status'];
						$status_icon = 'ℹ';
						
						// Determine status color for WordPress Health
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
						
						$total = 1; // For display purposes
						$wp_test_run   = (int) ( $test_counts['wordpress-health']['run'] ?? 0 );
						$wp_test_total = (int) ( $test_counts['wordpress-health']['total'] ?? 0 );
						?>
						<div class="wps-category-gauge" aria-label="<?php esc_attr_e( 'WordPress Site Health status', 'wpshadow' ); ?>">
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
									<div class="wps-category-gauge-count">
										<?php
										echo esc_html(
											sprintf(
												/* translators: 1: run tests, 2: total tests */
												__( 'Tests run: %1$d/%2$d', 'wpshadow' ),
												$wp_test_run,
												$wp_test_total
											)
										);
										?>
									</div>
								</div>
							</div>
						</div>
						<?php
						continue;
					}
					
					$cat_findings = $findings_by_category[ $cat_key ] ?? array();
					$total        = count( $cat_findings );

					$critical_count = count(
						array_filter(
							$cat_findings,
							function ( $f ) {
								return isset( $f['color'] ) && '#f44336' === $f['color'];
							}
						)
					);

					if ( 0 === $total ) {
						$status_text  = __( 'Excellent', 'wpshadow' );
						$status_icon  = '✓';
						$status_color = '#10b981';
					} elseif ( 0 === $critical_count ) {
						$status_text  = __( 'Good', 'wpshadow' );
						$status_icon  = '✓';
						$status_color = '#10b981';
					} elseif ( $critical_count < $total / 2 ) {
						$status_text  = __( 'Fair', 'wpshadow' );
						$status_icon  = '◐';
						$status_color = '#f59e0b';
					} else {
						$status_text  = __( 'Needs Work', 'wpshadow' );
						$status_icon  = '✕';
						$status_color = '#ef4444';
					}

					$threat_total = 0;
					foreach ( $cat_findings as $finding ) {
						$threat_total += isset( $finding['threat_level'] ) ? $finding['threat_level'] : 50;
					}
					$gauge_percent = $total > 0 ? min( 100, $threat_total / $total ) : 0;
					$gauge_percent = 100 - $gauge_percent; // Invert: higher is better
					
					// Show 0% until diagnostics have been run
					if ( $never_run ) {
						$gauge_percent = 0;
					}
					
					$gauge_color   = $get_threat_gauge_color( 100 - $gauge_percent );
					$cat_test_run   = (int) ( $test_counts[ $cat_key ]['run'] ?? 0 );
					$cat_test_total = (int) ( $test_counts[ $cat_key ]['total'] ?? 0 );
					?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow&category=' . $cat_key ) ); ?>" 
						class="wps-category-gauge" 
						data-category="<?php echo esc_attr( $cat_key ); ?>"
						aria-label="
						<?php
						echo esc_attr(
							sprintf(
								/* translators: 1: category name, 2: health percentage */
								__( '%1$s health: %2$d%%. Click to view details', 'wpshadow' ),
								isset( $meta['label'] ) ? $meta['label'] : ucfirst( $cat_key ),
								(int) $gauge_percent
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
								<div class="wps-category-gauge-count">
									<?php
									echo esc_html(
										sprintf(
											/* translators: 1: run tests, 2: total associated tests */
											__( 'Tests run: %1$d/%2$d', 'wpshadow' ),
											$cat_test_run,
											$cat_test_total
										)
									);
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
