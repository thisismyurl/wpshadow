<?php
/**
 * Impact Dashboard Widget
 *
 * Shows user's impact and value from using WPShadow.
 * Implements #9 Everything Has a KPI and #8 Inspire Confidence.
 *
 * @package    WPShadow
 * @subpackage Analytics
 * @since      1.2601.2200
 */

declare(strict_types=1);

namespace WPShadow\Analytics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Impact Dashboard Widget Class
 *
 * Displays usage statistics and ROI on WordPress dashboard.
 *
 * @since 1.2601.2200
 */
class Impact_Dashboard_Widget {

	/**
	 * Initialize the dashboard widget.
	 *
	 * @since 1.2601.2200
	 * @return void
	 */
	public static function init() {
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'register_widget' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	/**
	 * Register the dashboard widget.
	 *
	 * @since 1.2601.2200
	 * @return void
	 */
	public static function register_widget() {
		wp_add_dashboard_widget(
			'wpshadow_impact_widget',
			__( '🚀 WPShadow Impact', 'wpshadow' ),
			array( __CLASS__, 'render_widget' )
		);
	}

	/**
	 * Enqueue widget assets.
	 *
	 * @since  1.2601.2200
	 * @param  string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook ) {
		if ( 'index.php' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'wpshadow-impact-widget',
			WPSHADOW_URL . 'assets/css/impact-widget.css',
			array(),
			WPSHADOW_VERSION
		);
	}

	/**
	 * Render the dashboard widget.
	 *
	 * @since 1.2601.2200
	 * @return void
	 */
	public static function render_widget() {
		// Get stats for this month
		$stats_month = Usage_Tracker::get_stats( 30 );
		$stats_all   = Usage_Tracker::get_stats( 0 );

		// Calculate time saved
		$time_saved_month = $stats_month['total_time_saved'] ?? 0;
		$time_saved_all   = $stats_all['total_time_saved'] ?? 0;

		// Get hourly rate from settings (default $100/hour)
		$hourly_rate = get_option( 'wpshadow_hourly_rate', 100 );

		// Calculate money saved
		$money_saved_month = Usage_Tracker::calculate_money_saved( $time_saved_month, $hourly_rate );
		$money_saved_all   = Usage_Tracker::calculate_money_saved( $time_saved_all, $hourly_rate );

		// Get most used utility
		$most_used = Usage_Tracker::get_most_used( 30 );

		// Format time saved
		$hours_saved_month = round( $time_saved_month / 60, 1 );
		$hours_saved_all   = round( $time_saved_all / 60, 1 );

		?>
		<div class="wpshadow-impact-widget">
			
			<!-- This Month Stats -->
			<div class="wpshadow-stat-card wpshadow-stat-primary">
				<div class="wpshadow-stat-icon">⏱️</div>
				<div class="wpshadow-stat-content">
					<div class="wpshadow-stat-value"><?php echo esc_html( $hours_saved_month ); ?> <?php esc_html_e( 'hours', 'wpshadow' ); ?></div>
					<div class="wpshadow-stat-label"><?php esc_html_e( 'Saved This Month', 'wpshadow' ); ?></div>
				</div>
			</div>

			<div class="wpshadow-stat-card wpshadow-stat-success">
				<div class="wpshadow-stat-icon">💰</div>
				<div class="wpshadow-stat-content">
					<div class="wpshadow-stat-value">$<?php echo esc_html( number_format( $money_saved_month, 0 ) ); ?></div>
					<div class="wpshadow-stat-label"><?php esc_html_e( 'Value This Month', 'wpshadow' ); ?></div>
				</div>
			</div>

			<!-- All Time Stats -->
			<div class="wpshadow-stat-card wpshadow-stat-info">
				<div class="wpshadow-stat-icon">📊</div>
				<div class="wpshadow-stat-content">
					<div class="wpshadow-stat-value"><?php echo esc_html( $hours_saved_all ); ?> <?php esc_html_e( 'hours', 'wpshadow' ); ?></div>
					<div class="wpshadow-stat-label"><?php esc_html_e( 'Saved All Time', 'wpshadow' ); ?></div>
				</div>
			</div>

			<div class="wpshadow-stat-card wpshadow-stat-warning">
				<div class="wpshadow-stat-icon">🎯</div>
				<div class="wpshadow-stat-content">
					<div class="wpshadow-stat-value">$<?php echo esc_html( number_format( $money_saved_all, 0 ) ); ?></div>
					<div class="wpshadow-stat-label"><?php esc_html_e( 'Total Value', 'wpshadow' ); ?></div>
				</div>
			</div>

			<!-- Most Used Feature -->
			<?php if ( ! empty( $most_used['utility'] ) ) : ?>
			<div class="wpshadow-most-used">
				<h4><?php esc_html_e( 'Your Most Used Feature', 'wpshadow' ); ?></h4>
				<div class="wpshadow-feature-highlight">
					<strong><?php echo esc_html( Usage_Tracker::get_utility_label( $most_used['utility'] ) ); ?></strong>
					<span class="wpshadow-feature-stats">
						<?php
						/* translators: 1: usage count, 2: time saved in minutes */
						printf(
							esc_html__( 'Used %1$d times · Saved %2$d minutes', 'wpshadow' ),
							esc_html( $most_used['count'] ),
							esc_html( $most_used['saved'] )
						);
						?>
					</span>
				</div>
			</div>
			<?php endif; ?>

			<!-- Breakdown -->
			<?php if ( ! empty( $stats_month['usage_counts'] ) ) : ?>
			<div class="wpshadow-breakdown">
				<h4><?php esc_html_e( 'This Month\'s Activity', 'wpshadow' ); ?></h4>
				<table class="wpshadow-usage-table">
					<tbody>
						<?php
						arsort( $stats_month['usage_counts'] );
						foreach ( $stats_month['usage_counts'] as $utility => $count ) :
							$time_saved = $stats_month['time_saved'][ $utility ] ?? 0;
							?>
						<tr>
							<td class="wpshadow-utility-name">
								<?php echo esc_html( Usage_Tracker::get_utility_label( $utility ) ); ?>
							</td>
							<td class="wpshadow-utility-count">
								<?php
								/* translators: %d: number of times used */
								printf( esc_html__( '%d uses', 'wpshadow' ), esc_html( $count ) );
								?>
							</td>
							<td class="wpshadow-utility-saved">
								<?php
								/* translators: %d: minutes saved */
								printf( esc_html__( '%d min', 'wpshadow' ), esc_html( $time_saved ) );
								?>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php endif; ?>

			<!-- Empty State -->
			<?php if ( empty( $stats_month['usage_counts'] ) ) : ?>
			<div class="wpshadow-empty-state">
				<p><?php esc_html_e( 'Start using WPShadow utilities to track your time savings!', 'wpshadow' ); ?></p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-utilities' ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Explore Utilities', 'wpshadow' ); ?>
				</a>
			</div>
			<?php endif; ?>

			<!-- Footer Actions -->
			<div class="wpshadow-widget-footer">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-reports' ) ); ?>">
					<?php esc_html_e( 'View Full Reports', 'wpshadow' ); ?>
				</a>
				<span class="separator">|</span>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-utilities' ) ); ?>">
					<?php esc_html_e( 'Explore Utilities', 'wpshadow' ); ?>
				</a>
				<span class="separator">|</span>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-settings&tab=analytics' ) ); ?>">
					<?php esc_html_e( 'Settings', 'wpshadow' ); ?>
				</a>
			</div>

			<!-- ROI Message -->
			<?php if ( $money_saved_month > 0 ) : ?>
			<div class="wpshadow-roi-message">
				<?php
				/* translators: 1: money saved this month */
				printf(
					esc_html__( '💡 Based on your $%d/hour rate, WPShadow saved you $%s this month!', 'wpshadow' ),
					esc_html( $hourly_rate ),
					esc_html( number_format( $money_saved_month, 0 ) )
				);
				?>
			</div>
			<?php endif; ?>

		</div>

		<style>
		.wpshadow-impact-widget {
			margin: -12px;
			padding: 20px;
		}
		.wpshadow-stat-card {
			display: flex;
			align-items: center;
			padding: 15px;
			margin-bottom: 12px;
			border-radius: 6px;
			border-left: 4px solid;
		}
		.wpshadow-stat-primary { 
			background: #f0f6fc; 
			border-left-color: #0073aa;
		}
		.wpshadow-stat-success { 
			background: #f0f9f4; 
			border-left-color: #46b450;
		}
		.wpshadow-stat-info { 
			background: #f8f9fa; 
			border-left-color: #6c757d;
		}
		.wpshadow-stat-warning { 
			background: #fffbf0; 
			border-left-color: #f0b429;
		}
		.wpshadow-stat-icon {
			font-size: 32px;
			margin-right: 15px;
		}
		.wpshadow-stat-content {
			flex: 1;
		}
		.wpshadow-stat-value {
			font-size: 24px;
			font-weight: 600;
			line-height: 1.2;
		}
		.wpshadow-stat-label {
			font-size: 12px;
			color: #666;
			margin-top: 2px;
		}
		.wpshadow-most-used,
		.wpshadow-breakdown {
			margin-top: 20px;
			padding-top: 20px;
			border-top: 1px solid #ddd;
		}
		.wpshadow-most-used h4,
		.wpshadow-breakdown h4 {
			margin: 0 0 12px 0;
			font-size: 13px;
			font-weight: 600;
			text-transform: uppercase;
			color: #666;
		}
		.wpshadow-feature-highlight {
			display: flex;
			justify-content: space-between;
			align-items: center;
			padding: 12px;
			background: #f9f9f9;
			border-radius: 4px;
		}
		.wpshadow-feature-stats {
			font-size: 12px;
			color: #666;
		}
		.wpshadow-usage-table {
			width: 100%;
			border-collapse: collapse;
		}
		.wpshadow-usage-table td {
			padding: 8px 4px;
			border-bottom: 1px solid #f0f0f0;
			font-size: 13px;
		}
		.wpshadow-usage-table td:last-child {
			text-align: right;
		}
		.wpshadow-utility-count,
		.wpshadow-utility-saved {
			color: #666;
			white-space: nowrap;
		}
		.wpshadow-empty-state {
			text-align: center;
			padding: 40px 20px;
		}
		.wpshadow-empty-state p {
			margin-bottom: 15px;
			color: #666;
		}
		.wpshadow-widget-footer {
			margin-top: 20px;
			padding-top: 15px;
			border-top: 1px solid #ddd;
			text-align: center;
			font-size: 12px;
		}
		.wpshadow-widget-footer a {
			text-decoration: none;
		}
		.wpshadow-widget-footer .separator {
			margin: 0 8px;
			color: #ccc;
		}
		.wpshadow-roi-message {
			margin-top: 15px;
			padding: 12px;
			background: #fffbf0;
			border-left: 3px solid #f0b429;
			border-radius: 4px;
			font-size: 13px;
		}
		</style>
		<?php
	}
}
