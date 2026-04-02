<?php
/**
 * Trend Chart Component
 *
 * Displays 30-day health score trends and KPI improvements
 * using lightweight SVG-based visualization.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Core;

/**
 * Generates trend charts for KPI visualization
 */
class Trend_Chart {

	/**
	 * Get score history for the past 30 days
	 *
	 * @return array Historical data points.
	 */
	public static function get_score_history() {
		$history = get_option( 'wpshadow_score_history', array() );

		if ( ! is_array( $history ) ) {
			$history = array();
		}

		// Get last 30 days of data
		$cutoff_date = gmdate( 'Y-m-d', strtotime( '-30 days' ) );
		$history     = array_filter(
			$history,
			function ( $item ) use ( $cutoff_date ) {
				return isset( $item['date'] ) && $item['date'] >= $cutoff_date;
			}
		);

		// Ensure today's data is included
		$today = gmdate( 'Y-m-d' );
		if ( ! self::history_has_date( $history, $today ) ) {
			$current_health = get_option( 'wpshadow_health_status', array() );
			$score          = isset( $current_health['score'] ) ? (int) $current_health['score'] : 0;
			$history[]      = array(
				'date'  => $today,
				'score' => $score,
			);
		}

		return array_values( $history ); // Re-index
	}

	/**
	 * Check if history contains data for a specific date
	 *
	 * @param array  $history Historical data.
	 * @param string $date Date to check (Y-m-d format).
	 * @return bool
	 */
	private static function history_has_date( $history, $date ) {
		foreach ( $history as $item ) {
			if ( isset( $item['date'] ) && $item['date'] === $date ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Record a score in history
	 *
	 * @param int $score Score (0-100).
	 * @return void
	 */
	public static function record_score( $score ) {
		$history = get_option( 'wpshadow_score_history', array() );

		if ( ! is_array( $history ) ) {
			$history = array();
		}

		$today = gmdate( 'Y-m-d' );

		// Update if today already exists, otherwise add
		$found = false;
		foreach ( $history as &$item ) {
			if ( isset( $item['date'] ) && $item['date'] === $today ) {
				$item['score'] = (int) $score;
				$found         = true;
				break;
			}
		}

		if ( ! $found ) {
			$history[] = array(
				'date'  => $today,
				'score' => (int) $score,
			);
		}

		// Keep only last 90 days
		$cutoff_date = gmdate( 'Y-m-d', strtotime( '-90 days' ) );
		$history     = array_filter(
			$history,
			function ( $item ) use ( $cutoff_date ) {
				return isset( $item['date'] ) && $item['date'] >= $cutoff_date;
			}
		);

		update_option( 'wpshadow_score_history', array_values( $history ) );
	}

	/**
	 * Render a line chart of health scores
	 *
	 * @return void Outputs SVG and HTML.
	 */
	public static function render_trend_chart() {
		$history = self::get_score_history();

		if ( count( $history ) < 2 ) {
			?>
			<div class="wps-p-40-rounded-8">
				<p><?php esc_html_e( 'Not enough data yet. Check back in a few days for trend visualization.', 'wpshadow' ); ?></p>
			</div>
			<?php
			return;
		}

		// Prepare data for chart
		$scores = array_map(
			function ( $item ) {
				return isset( $item['score'] ) ? (int) $item['score'] : 0;
			},
			$history
		);

		$min_score = min( $scores );
		$max_score = max( $scores );
		$range     = max( 1, $max_score - $min_score );

		// Calculate trend direction
		$first_score     = $scores[0];
		$last_score      = end( $scores );
		$trend_direction = $last_score > $first_score ? 'up' : ( $last_score < $first_score ? 'down' : 'flat' );
		$trend_pct       = $range > 0 ? round( ( ( $last_score - $first_score ) / $range ) * 100, 1 ) : 0;

		$chart_width  = 600;
		$chart_height = 300;
		$padding      = 40;
		$point_count  = count( $scores );
		$x_step       = ( $chart_width - 2 * $padding ) / max( 1, $point_count - 1 );
		$y_scale      = ( $chart_height - 2 * $padding ) / max( 1, $range );

		// Generate SVG path
		$path_data = '';
		foreach ( $scores as $idx => $score ) {
			$x          = $padding + $idx * $x_step;
			$y          = $chart_height - $padding - ( $score - $min_score ) * $y_scale;
			$path_data .= ( $idx === 0 ? 'M' : 'L' ) . $x . ' ' . $y . ' ';
		}
		?>

		<div class="wpshadow-trend-chart" class="wps-p-20-rounded-8">

			<!-- Header -->
			<div class="wps-flex-items-center-justify-space-between">
				<h3 class="wps-m-0">
					<?php esc_html_e( 'Health Score Trend (30 Days)', 'wpshadow' ); ?>
				</h3>
				<div class="wps-flex-gap-8-items-center">
					<?php if ( $trend_direction === 'up' ) : ?>
						<span style="color: #10b981; font-weight: bold;">📈 +<?php echo (float) abs( $trend_pct ); ?>%</span>
					<?php elseif ( $trend_direction === 'down' ) : ?>
						<span style="color: #ef4444; font-weight: bold;">📉 -<?php echo (float) abs( $trend_pct ); ?>%</span>
					<?php else : ?>
						<span class="wps-font-bold" style="color: #f59e0b;">➡️ Stable</span>
					<?php endif; ?>
				</div>
			</div>

			<!-- SVG Chart -->
			<svg width="<?php echo (int) $chart_width; ?>" height="<?php echo (int) $chart_height; ?>" class="wps-block-m-0-rounded-4">

				<!-- Grid lines (every 10 points) -->
				<?php for ( $i = 0; $i <= 100; $i += 10 ) : ?>
					<?php
					$grid_y = $chart_height - $padding - ( ( $i - $min_score ) / max( 1, $range ) ) * ( $chart_height - 2 * $padding );
					if ( $grid_y >= $padding && $grid_y <= $chart_height - $padding ) :
						?>
						<line x1="<?php echo (int) $padding; ?>" y1="<?php echo (float) $grid_y; ?>" x2="<?php echo (int) ( $chart_width - $padding ); ?>" y2="<?php echo (float) $grid_y; ?>" stroke="#e5e7eb" stroke-width="1" />
						<text x="<?php echo (int) ( $padding - 10 ); ?>" y="<?php echo (float) ( $grid_y + 4 ); ?>" font-size="12" fill="#9ca3af" text-anchor="end"><?php echo (int) $i; ?>%</text>
					<?php endif; ?>
				<?php endfor; ?>

				<!-- Path (trend line) -->
				<!-- phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG path data is constructed from numeric calculations only -->
				<path d="<?php echo $path_data; ?>" stroke="#667eea" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" />

				<!-- Area under curve -->
				<!-- phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG path data is constructed from numeric calculations only -->
				<path d="<?php echo $path_data; ?> L <?php echo (float) ( $chart_width - $padding ); ?> <?php echo (float) ( $chart_height - $padding ); ?> L <?php echo (float) $padding; ?> <?php echo (float) ( $chart_height - $padding ); ?> Z" fill="#667eea" opacity="0.1" />

				<!-- Data points -->
				<?php foreach ( $scores as $idx => $score ) : ?>
					<?php
					$x          = $padding + $idx * $x_step;
					$y          = $chart_height - $padding - ( $score - $min_score ) * $y_scale;
					$fill_color = $idx === count( $scores ) - 1 ? '#10b981' : '#667eea';
					?>
					<circle cx="<?php echo (float) $x; ?>" cy="<?php echo (float) $y; ?>" r="4" fill="<?php echo esc_attr( $fill_color ); ?>" stroke="white" stroke-width="2" />

					<!-- Accessible hover label (via title element) -->
					<title><?php echo esc_attr( sprintf( '%s: %d%%', isset( $history[ $idx ]['date'] ) ? $history[ $idx ]['date'] : 'N/A', $score ) ); ?></title>
				<?php endforeach; ?>

			</svg>

			<!-- Legend -->
			<div class="wps-flex-gap-24-justify-center">
				<div class="wps-flex-gap-6-items-center">
					<span class="wps-rounded-2"></span>
					<?php esc_html_e( 'Historical Score', 'wpshadow' ); ?>
				</div>
				<div class="wps-flex-gap-6-items-center">
					<span class="wps-rounded-50%"></span>
					<?php esc_html_e( 'Latest Score', 'wpshadow' ); ?>
				</div>
			</div>

		</div>

		<?php
	}

	/**
	 * Get trend summary statistics
	 *
	 * @return array Trend statistics.
	 */
	public static function get_trend_stats() {
		$history = self::get_score_history();

		if ( count( $history ) < 1 ) {
			return array(
				'current_score'    => 0,
				'start_score'      => 0,
				'improvement'      => 0,
				'improvement_pct'  => 0,
				'days_tracked'     => 0,
				'avg_daily_change' => 0,
			);
		}

		$scores = array_map(
			function ( $item ) {
				return isset( $item['score'] ) ? (int) $item['score'] : 0;
			},
			$history
		);

		$current_score   = end( $scores );
		$start_score     = reset( $scores );
		$improvement     = $current_score - $start_score;
		$improvement_pct = $start_score > 0 ? round( ( $improvement / $start_score ) * 100, 1 ) : 0;

		return array(
			'current_score'    => $current_score,
			'start_score'      => $start_score,
			'improvement'      => $improvement,
			'improvement_pct'  => $improvement_pct,
			'days_tracked'     => count( $history ),
			'avg_daily_change' => count( $history ) > 1 ? round( $improvement / ( count( $history ) - 1 ), 2 ) : 0,
		);
	}

	/**
	 * Record a finding resolution (Phase 3: KPI Wiring)
	 *
	 * @param string $finding_id Finding identifier.
	 * @param string $status Status of resolution (fixed, ignored, delegated).
	 * @return void
	 */
	public static function record_finding_resolved( $finding_id, $status = 'fixed' ) {
		$resolutions = get_option( 'wpshadow_finding_resolutions', array() );

		if ( ! is_array( $resolutions ) ) {
			$resolutions = array();
		}

		$resolutions[] = array(
			'finding_id' => $finding_id,
			'status'     => $status,
			'date'       => gmdate( 'Y-m-d H:i:s' ),
			'user_id'    => get_current_user_id(),
		);

		// Keep only last 90 days (privacy-first, per Philosophy #10)
		$cutoff_date = gmdate( 'Y-m-d', strtotime( '-90 days' ) );
		$resolutions = array_filter(
			$resolutions,
			function ( $item ) use ( $cutoff_date ) {
				return isset( $item['date'] ) && substr( $item['date'], 0, 10 ) >= $cutoff_date;
			}
		);

		update_option( 'wpshadow_finding_resolutions', $resolutions );
	}
}
