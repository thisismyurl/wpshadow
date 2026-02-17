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
		'wpshadow',
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

	// Show 0% until diagnostics have been run
	if ( $never_run ) {
		return array(
			'score'   => 0,
			'status'  => __( 'Not Scanned', 'wpshadow' ),
			'color'   => '#999999',
			'message' => __( 'Run your first scan to see your site health.', 'wpshadow' ),
		);
	}

	if ( empty( $findings ) ) {
		return array(
			'score'   => 100,
			'status'  => __( 'Excellent', 'wpshadow' ),
			'color'   => '#2e7d32',
			'message' => __( 'No issues detected. Your site is in excellent health!', 'wpshadow' ),
		);
	}

	$critical_count = 0;
	$high_count     = 0;
	$medium_count   = 0;

	foreach ( $findings as $finding ) {
		$threat = isset( $finding['threat_level'] ) ? $finding['threat_level'] : 50;

		if ( $threat >= 75 ) {
			++$critical_count;
		} elseif ( $threat >= 50 ) {
			++$high_count;
		} else {
			++$medium_count;
		}
	}

	$total_findings = count( $findings );
	$weighted_score = 100 - ( ( $critical_count * 30 + $high_count * 15 + $medium_count * 5 ) / $total_findings );
	$weighted_score = max( 0, min( 100, (int) $weighted_score ) );

	if ( $weighted_score >= 80 ) {
		$status  = __( 'Good', 'wpshadow' );
		$color   = '#2e7d32';
		$message = sprintf(
			/* translators: %d: number of issues found */
			__( 'Your site is in good health with %d issue(s) to address.', 'wpshadow' ),
			$total_findings
		);
	} elseif ( $weighted_score >= 60 ) {
		$status  = __( 'Fair', 'wpshadow' );
		$color   = '#f57c00';
		$message = sprintf(
			/* translators: 1: total issues count, 2: critical issues count */
			__( 'Your site needs attention: %1$d issue(s) detected, including %2$d critical issue(s).', 'wpshadow' ),
			$total_findings,
			$critical_count
		);
	} else {
		$status  = __( 'Poor', 'wpshadow' );
		$color   = '#c62828';
		$message = sprintf(
			/* translators: %d: number of critical issues */
			__( 'Your site has %d critical issue(s) that need immediate attention.', 'wpshadow' ),
			$critical_count
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

	$findings_by_category = array();
	foreach ( $category_meta as $cat_key => $meta ) {
		$findings_by_category[ $cat_key ] = array_filter(
			$findings,
			function ( $f ) use ( $cat_key ) {
				return isset( $f['category'] ) && $f['category'] === $cat_key;
			}
		);
	}

	// Calculate overall health
	$overall_health = \wpshadow_calculate_overall_health( $findings_by_category, $category_meta );

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

				<svg width="200" height="200" viewBox="0 0 200 200" class="wps-health-gauge-svg" aria-labelledby="overall-health-title" role="img">
					<title id="overall-health-title">
						<?php
						echo esc_html(
							sprintf(
								/* translators: %d: health score percentage */
								__( 'Overall site health: %d%%', 'wpshadow' ),
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
						stroke-dasharray="<?php echo (int) ( ( isset( $overall_health['score'] ) ? $overall_health['score'] : 0 ) / 100 * 534 ); ?> 534"
						stroke-linecap="round" transform="rotate(-90 100 100)"
						class="wps-gauge-progress" />
					<!-- Center text -->
				<text x="100" y="110" text-anchor="middle" font-size="48" font-weight="bold" fill="<?php echo esc_attr( isset( $overall_health['color'] ) ? $overall_health['color'] : '#ccc' ); ?>"><?php echo isset( $overall_health['score'] ) ? (int) $overall_health['score'] : 0; ?>%</text>
				<text x="100" y="135" text-anchor="middle" font-size="16" fill="#666"><?php echo esc_html( isset( $overall_health['status'] ) && $overall_health['status'] ? $overall_health['status'] : 'Unknown' ); ?></text>
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
				foreach ( $category_meta as $cat_key => $meta ) :
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
									if ( 0 === $total ) {
										echo esc_html( __( 'No issues', 'wpshadow' ) );
									} else {
										echo esc_html(
											sprintf(
												/* translators: %d: number of issues found */
												__( '%d issues found', 'wpshadow' ),
												$total
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
