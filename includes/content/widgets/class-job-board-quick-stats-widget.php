<?php
/**
 * Job Board Quick Stats Widget
 *
 * Dashboard widget showing job board statistics and quick actions.
 *
 * @package WPShadow
 * @subpackage Content
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Widgets;

use WPShadow\Core\Hook_Subscriber_Base;
use WPShadow\JobPostings\Job_Application_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Job Board Quick Stats Widget Class
 *
 * @since 1.6093.1200
 */
class Job_Board_Quick_Stats_Widget extends Hook_Subscriber_Base {

	/**
	 * Get hooks to subscribe to.
	 *
	 * @since 1.6093.1200
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'wp_dashboard_setup'    => 'register_widget',
			'admin_enqueue_scripts' => 'enqueue_assets',
		);
	}

	/**
	 * Enqueue widget stylesheet on dashboard.
	 *
	 * @since 1.6093.1200
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook ) {
		if ( 'index.php' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'wpshadow-job-board-quick-stats-widget',
			WPSHADOW_URL . 'assets/css/job-board-quick-stats-widget.css',
			array(),
			WPSHADOW_VERSION
		);
	}

	/**
	 * Register the dashboard widget.
	 *
	 * @since 1.6093.1200
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
	 * @since 1.6093.1200
	 */
	public static function render_widget() {
		// Get statistics
		$active_jobs_count = wp_count_posts( 'wps_job_posting' )->publish ?? 0;
		$draft_jobs_count = wp_count_posts( 'wps_job_posting' )->draft ?? 0;
		$total_applications = Job_Application_Tracker::get_total_applications();
		$new_applications   = Job_Application_Tracker::get_applications_count_by_status( 'new' );

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
						<p class="wpshadow-stat-number <?php echo $new_applications > 0 ? 'wpshadow-stat-number-has-new' : 'wpshadow-stat-number-zero-new'; ?>">
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

			<div class="wpshadow-widget-actions">
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
		<?php
	}
}
