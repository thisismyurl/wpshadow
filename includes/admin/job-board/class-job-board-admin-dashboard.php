<?php
/**
 * Job Board Admin Dashboard
 *
 * Admin dashboard for managing jobs and applications.
 *
 * @package WPShadow
 * @subpackage Content
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\Hook_Subscriber_Base;
use WPShadow\JobPostings\Job_Application_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Job Board Admin Dashboard Class
 *
 * @since 1.6093.1200
 */
class Job_Board_Admin_Dashboard extends Hook_Subscriber_Base {

	/**
	 * Get hooks to subscribe to.
	 *
	 * @since 1.6093.1200
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'admin_menu'      => 'add_admin_menu',
			'load-wps_job_posting' => 'add_dashboard_column',
		);
	}

	/**
	 * Add job board admin menu.
	 *
	 * @since 1.6093.1200
	 */
	public static function add_admin_menu() {
		add_submenu_page(
			'edit.php?post_type=wps_job_posting',
			__( 'Job Board Dashboard', 'wpshadow' ),
			__( 'Dashboard', 'wpshadow' ),
			'manage_options',
			'job-board-dashboard',
			array( __CLASS__, 'render_dashboard' ),
			1
		);

		add_submenu_page(
			'edit.php?post_type=wps_job_posting',
			__( 'Applications', 'wpshadow' ),
			__( 'Applications', 'wpshadow' ),
			'manage_options',
			'job-applications',
			array( __CLASS__, 'render_applications_page' ),
			2
		);

		add_submenu_page(
			'edit.php?post_type=wps_job_posting',
			__( 'Job Board Settings', 'wpshadow' ),
			__( 'Settings', 'wpshadow' ),
			'manage_options',
			'job-board-settings',
			array( __CLASS__, 'render_settings_page' ),
			3
		);
	}

	/**
	 * Render job board dashboard.
	 *
	 * @since 1.6093.1200
	 */
	public static function render_dashboard() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Job Board Dashboard', 'wpshadow' ); ?></h1>

			<?php do_action( 'wpshadow_after_page_header' ); ?>

			<div class="wpshadow-dashboard-grid">
				<?php self::render_stats_cards(); ?>
			</div>

			<div class="wpshadow-dashboard-widgets">
				<?php self::render_recent_applications(); ?>
				<?php self::render_active_jobs(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render statistics cards.
	 *
	 * @since 1.6093.1200
	 */
	private static function render_stats_cards() {
		// Total active jobs
		$active_jobs = wp_count_posts( 'wps_job_posting' );
		$active_count = $active_jobs->publish ?? 0;

		// Application counts
		$total_applications = Job_Application_Tracker::get_total_applications();
		$new_applications   = Job_Application_Tracker::get_applications_count_by_status( 'new' );

		$stats = array(
			array(
				'label'  => __( 'Active Jobs', 'wpshadow' ),
				'value'  => $active_count,
				'icon'   => '📋',
				'link'   => admin_url( 'edit.php?post_type=wps_job_posting&post_status=publish' ),
			),
			array(
				'label'  => __( 'Total Applications', 'wpshadow' ),
				'value'  => $total_applications,
				'icon'   => '📝',
				'link'   => admin_url( 'admin.php?page=job-applications' ),
			),
			array(
				'label'  => __( 'New Applications', 'wpshadow' ),
				'value'  => $new_applications,
				'icon'   => '🆕',
				'link'   => admin_url( 'admin.php?page=job-applications&status=new' ),
				'highlight' => $new_applications > 0,
			),
		);

		foreach ( $stats as $stat ) {
			$class = isset( $stat['highlight'] ) && $stat['highlight'] ? 'wpshadow-stat-card-highlight' : '';
			?>
			<div class="wpshadow-stat-card <?php echo esc_attr( $class ); ?>">
				<a href="<?php echo esc_url( $stat['link'] ); ?>">
					<span class="wpshadow-stat-icon"><?php echo $stat['icon']; ?></span>
					<div class="wpshadow-stat-content">
						<p class="wpshadow-stat-label"><?php echo esc_html( $stat['label'] ); ?></p>
						<p class="wpshadow-stat-value"><?php echo absint( $stat['value'] ); ?></p>
					</div>
				</a>
			</div>
			<?php
		}
	}

	/**
	 * Render recent applications widget.
	 *
	 * @since 1.6093.1200
	 */
	private static function render_recent_applications() {
		$recent = Job_Application_Tracker::get_recent_applications( 5 );

		if ( empty( $recent ) ) {
			return;
		}

		?>
		<div class="wpshadow-dashboard-widget">
			<h3><?php esc_html_e( 'Recent Applications', 'wpshadow' ); ?></h3>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Applicant', 'wpshadow' ); ?></th>
						<th><?php esc_html_e( 'Job', 'wpshadow' ); ?></th>
						<th><?php esc_html_e( 'Status', 'wpshadow' ); ?></th>
						<th><?php esc_html_e( 'Date', 'wpshadow' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $recent as $application ) : ?>
						<tr>
							<td><?php echo esc_html( $application->applicant_name ); ?></td>
							<td><?php echo esc_html( $application->post_title ); ?></td>
							<td><span class="wpshadow-badge wpshadow-badge-<?php echo esc_attr( $application->status ); ?>"><?php echo esc_html( ucfirst( $application->status ) ); ?></span></td>
							<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $application->applied_at ) ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Render active jobs widget.
	 *
	 * @since 1.6093.1200
	 */
	private static function render_active_jobs() {
		$jobs = get_posts( array(
			'post_type'      => 'wps_job_posting',
			'posts_per_page' => 5,
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );

		if ( empty( $jobs ) ) {
			return;
		}

		?>
		<div class="wpshadow-dashboard-widget">
			<h3><?php esc_html_e( 'Active Job Postings', 'wpshadow' ); ?></h3>
			<ul class="wpshadow-job-list">
				<?php foreach ( $jobs as $job ) : ?>
					<li>
						<a href="<?php echo esc_url( admin_url( "post.php?post={$job->ID}&action=edit" ) ); ?>">
							<?php echo esc_html( $job->post_title ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}

	/**
	 * Render applications page.
	 *
	 * @since 1.6093.1200
	 */
	public static function render_applications_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Job Applications', 'wpshadow' ); ?></h1>

			<?php do_action( 'wpshadow_after_page_header' ); ?>
			<!-- Applications list table will be rendered here -->
		</div>
		<?php
	}

	/**
	 * Render settings page.
	 *
	 * @since 1.6093.1200
	 */
	public static function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Job Board Settings', 'wpshadow' ); ?></h1>

			<?php do_action( 'wpshadow_after_page_header' ); ?>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'wpshadow_job_board' );
				do_settings_sections( 'wpshadow_job_board' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
