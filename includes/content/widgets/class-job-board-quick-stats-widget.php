<?php
/**
 * Job Board Quick Stats Widget
 *
 * Dashboard widget showing job board statistics and quick actions.
 *
 * @package WPShadow
 * @subpackage Content
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Widgets;

use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Job Board Quick Stats Widget Class
 *
 * @since 1.6050.0000
 */
class Job_Board_Quick_Stats_Widget extends Hook_Subscriber_Base {

	/**
	 * Get hooks to subscribe to.
	 *
	 * @since  1.6050.0000
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'wp_dashboard_setup' => 'register_widget',
		);
	}

	/**
	 * Register the dashboard widget.
	 *
	 * @since 1.6050.0000
	 */
	public static function register_widget() {
		wp_add_dashboard_widget(
			'wpshadow_job_board_stats',
			__( 'Job Board Quick Stats', 'wpshadow' ),
			array( __CLASS__, 'render_widget' )
		);
	}

	/**
	 * Render the widget.
	 *
	 * @since 1.6050.0000
	 */
	public static function render_widget() {
		global $wpdb;

		// Get statistics
		$active_jobs_count = wp_count_posts( 'wps_job_posting' )->publish ?? 0;
		$draft_jobs_count = wp_count_posts( 'wps_job_posting' )->draft ?? 0;
		$total_applications = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}wpshadow_job_applications"
		);
		$new_applications = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}wpshadow_job_applications WHERE status = 'new'"
		);

		?>
		<div class="wpshadow-job-board-stats-widget">
			<div class="wpshadow-stats-grid">
				<div class="wpshadow-stat-box">
					<span class="wpshadow-stat-icon">📋</span>
					<div class="wpshadow-stat-info">
						<p class="wpshadow-stat-label"><?php esc_html_e( 'Active Jobs', 'wpshadow' ); ?></p>
						<p class="wpshadow-stat-number"><?php echo absint( $active_jobs_count ); ?></p>
					</div>
				</div>

				<div class="wpshadow-stat-box">
					<span class="wpshadow-stat-icon">📝</span>
					<div class="wpshadow-stat-info">
						<p class="wpshadow-stat-label"><?php esc_html_e( 'Draft Jobs', 'wpshadow' ); ?></p>
						<p class="wpshadow-stat-number"><?php echo absint( $draft_jobs_count ); ?></p>
					</div>
				</div>

				<div class="wpshadow-stat-box">
					<span class="wpshadow-stat-icon">🎯</span>
					<div class="wpshadow-stat-info">
						<p class="wpshadow-stat-label"><?php esc_html_e( 'New Applications', 'wpshadow' ); ?></p>
						<p class="wpshadow-stat-number" style="color: <?php echo $new_applications > 0 ? '#d63638' : '#999'; ?>">
							<?php echo absint( $new_applications ); ?>
						</p>
					</div>
				</div>

				<div class="wpshadow-stat-box">
					<span class="wpshadow-stat-icon">📊</span>
					<div class="wpshadow-stat-info">
						<p class="wpshadow-stat-label"><?php esc_html_e( 'Total Applications', 'wpshadow' ); ?></p>
						<p class="wpshadow-stat-number"><?php echo absint( $total_applications ); ?></p>
					</div>
				</div>
			</div>

			<div class="wpshadow-widget-actions" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
				<p>
					<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=wps_job_posting' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Post New Job', 'wpshadow' ); ?>
					</a>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=wps_job_posting' ) ); ?>" class="button">
						<?php esc_html_e( 'Manage Jobs', 'wpshadow' ); ?>
					</a>
				</p>
				<p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=job-applications' ) ); ?>" class="button">
						<?php esc_html_e( 'View Applications', 'wpshadow' ); ?>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=job-board-dashboard' ) ); ?>" class="button">
						<?php esc_html_e( 'Dashboard', 'wpshadow' ); ?>
					</a>
				</p>
			</div>
		</div>

		<style type="text/css">
			.wpshadow-stats-grid {
				display: grid;
				grid-template-columns: 1fr 1fr;
				gap: 10px;
				margin-bottom: 10px;
			}

			.wpshadow-stat-box {
				padding: 12px;
				background: #f5f5f5;
				border-radius: 4px;
				display: flex;
				align-items: center;
				gap: 10px;
			}

			.wpshadow-stat-icon {
				font-size: 24px;
			}

			.wpshadow-stat-info {
				flex: 1;
			}

			.wpshadow-stat-label {
				margin: 0;
				font-size: 12px;
				color: #666;
			}

			.wpshadow-stat-number {
				margin: 4px 0 0 0;
				font-size: 18px;
				font-weight: bold;
				color: #333;
			}

			.wpshadow-widget-actions p {
				margin: 8px 0;
			}

			.wpshadow-widget-actions a.button {
				margin-right: 6px;
				margin-bottom: 6px;
				font-size: 12px;
				padding: 4px 8px;
			}
		</style>
		<?php
	}
}
