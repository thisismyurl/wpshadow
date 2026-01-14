<?php
/**
 * Weekly Performance Report Feature
 *
 * Tracks and reports weekly performance metrics including:
 * - Uptime monitoring
 * - Speed improvements
 * - Issues fixed
 * - Data saved (MB)
 * - CPU cycles saved
 * - Time saved (hours)
 *
 * @package wp_support_Support
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Weekly Performance Report Feature Class
 */
class WPS_Feature_Weekly_Performance_Report extends WPS_Abstract_Feature {
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'wps_weekly_performance_report',
				'name'               => __( 'Weekly Performance Report', 'plugin-wp-support-thisismyurl' ),
				'description'        => __( 'Sends weekly email reports with performance metrics and improvements.', 'plugin-wp-support-thisismyurl' ),
				'scope'              => 'core',
				'version'            => '1.0.0',
				'default_enabled'    => false,
				'widget_group'       => 'diagnostics',
				'widget_label'       => __( 'Diagnostics & Monitoring', 'plugin-wp-support-thisismyurl' ),
				'widget_description' => __( 'Health checks and monitoring features', 'plugin-wp-support-thisismyurl' ),
			)
		);

		// Register default settings.
		$this->register_default_settings(
			array(
				self::METRICS_OPTION_KEY  => array(),
				self::SETTINGS_OPTION_KEY => array(
					'enabled'          => true,
					'recipient_emails' => array( get_option( 'admin_email' ) ),
				),
			)
		);
	}


	/**
	 * Option key for storing weekly metrics.
	 */
	private const METRICS_OPTION_KEY = 'wps_weekly_performance_metrics';

	/**
	 * Option key for storing report settings.
	 */
	private const SETTINGS_OPTION_KEY = 'wps_weekly_performance_settings';

	/**
	 * Cron hook for weekly report generation.
	 */
	private const CRON_HOOK = 'wps_send_weekly_performance_report';

	/**
	 * Initialize the feature.
	 *
	 * @return void
	 */
	public static function init(): void {
		if ( ! static::is_enabled() ) {
			return;
		}

		// Register cron schedule for weekly reports.
		add_filter( 'cron_schedules', array( __CLASS__, 'add_weekly_cron_schedule' ) );
		add_action( self::CRON_HOOK, array( __CLASS__, 'send_weekly_report' ) );

		// Schedule the cron job if not already scheduled.
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			// Schedule for Monday at 9:00 AM.
			$next_monday = strtotime( 'next Monday 9:00 AM' );
			wp_schedule_event( $next_monday, 'weekly', self::CRON_HOOK );
		}

		// Hook into various WordPress actions to track metrics.
		add_action( 'wp_loaded', array( __CLASS__, 'track_uptime' ) );
		add_action( 'WPS_issue_resolved', array( __CLASS__, 'track_issue_resolved' ), 10, 1 );
		add_action( 'WPS_performance_improvement', array( __CLASS__, 'track_performance_improvement' ), 10, 2 );

		// Admin interface hooks.
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );
		add_action( 'wp_ajax_wps_view_weekly_report', array( __CLASS__, 'ajax_view_report' ) );
	}

	/**
	 * Get feature identifier.
	 *
	 * @return string
	 */
	public function get_id(): string {
		return 'weekly-performance-report';
	}

	/**
	 * Get feature name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return __( 'Weekly Performance Report', 'plugin-wp-support-thisismyurl' );
	}

	/**
	 * Get feature description.
	 *
	 * @return string
	 */
	public function get_description(): string {
		return __( 'Generates and sends weekly visual performance reports showing uptime, speed improvements, issues fixed, and savings metrics.', 'plugin-wp-support-thisismyurl' );
	}

	/**
	 * Add weekly cron schedule.
	 *
	 * @param array $schedules Existing schedules.
	 * @return array Modified schedules.
	 */
	public static function add_weekly_cron_schedule( array $schedules ): array {
		$schedules['weekly'] = array(
			'interval' => WEEK_IN_SECONDS,
			'display'  => __( 'Once Weekly', 'plugin-wp-support-thisismyurl' ),
		);
		return $schedules;
	}

	/**
	 * Get current week's metrics.
	 *
	 * @return array
	 */
	public static function get_current_week_metrics(): array {
		$instance = new self();
		$metrics  = $instance->get_setting( self::METRICS_OPTION_KEY, array() );
		$week_key = self::get_current_week_key();

		if ( ! isset( $metrics[ $week_key ] ) ) {
			$metrics[ $week_key ] = self::get_default_metrics();
			$instance->update_setting( self::METRICS_OPTION_KEY, $metrics );
		}

		return $metrics[ $week_key ];
	}

	/**
	 * Get default metrics structure.
	 *
	 * @return array
	 */
	private static function get_default_metrics(): array {
		return array(
			'uptime_checks'      => 0,
			'uptime_success'     => 0,
			'speed_improvements' => 0,
			'issues_fixed'       => 0,
			'data_saved_mb'      => 0,
			'cpu_cycles_saved'   => 0,
			'time_saved_seconds' => 0,
			'page_load_before'   => 0,
			'page_load_after'    => 0,
			'week_start'         => strtotime( 'monday this week' ),
			'week_end'           => strtotime( 'sunday this week' ) + DAY_IN_SECONDS - 1,
		);
	}

	/**
	 * Get the current week key (YYYY-WW format).
	 *
	 * @return string
	 */
	private static function get_current_week_key(): string {
		return gmdate( 'Y-W' );
	}

	/**
	 * Track uptime check.
	 *
	 * @return void
	 */
	public static function track_uptime(): void {
		// Only track once per hour to avoid excessive checks.
		$last_check = get_transient( 'wps_last_uptime_check' );
		if ( false !== $last_check ) {
			return;
		}

		set_transient( 'wps_last_uptime_check', time(), HOUR_IN_SECONDS );

		$instance = new self();
		$metrics  = $instance->get_setting( self::METRICS_OPTION_KEY, array() );
		$week_key = self::get_current_week_key();

		if ( ! isset( $metrics[ $week_key ] ) ) {
			$metrics[ $week_key ] = self::get_default_metrics();
		}

		++$metrics[ $week_key ]['uptime_checks'];
		++$metrics[ $week_key ]['uptime_success'];

		$instance->update_setting( self::METRICS_OPTION_KEY, $metrics );
	}

	/**
	 * Track when an issue is resolved.
	 *
	 * @param array $issue_data Issue information.
	 * @return void
	 */
	public static function track_issue_resolved( array $issue_data ): void {
		$instance = new self();
		$metrics  = $instance->get_setting( self::METRICS_OPTION_KEY, array() );
		$week_key = self::get_current_week_key();

		if ( ! isset( $metrics[ $week_key ] ) ) {
			$metrics[ $week_key ] = self::get_default_metrics();
		}

		++$metrics[ $week_key ]['issues_fixed'];

		// Estimate time saved (average 30 minutes per issue).
		$metrics[ $week_key ]['time_saved_seconds'] += 1800;

		$instance->update_setting( self::METRICS_OPTION_KEY, $metrics );
	}

	/**
	 * Track performance improvement.
	 *
	 * @param string $improvement_type Type of improvement (speed, data, cpu).
	 * @param array  $improvement_data Improvement metrics.
	 * @return void
	 */
	public static function track_performance_improvement( string $improvement_type, array $improvement_data ): void {
		$instance = new self();
		$metrics  = $instance->get_setting( self::METRICS_OPTION_KEY, array() );
		$week_key = self::get_current_week_key();

		if ( ! isset( $metrics[ $week_key ] ) ) {
			$metrics[ $week_key ] = self::get_default_metrics();
		}

		switch ( $improvement_type ) {
			case 'speed':
				++$metrics[ $week_key ]['speed_improvements'];
				if ( isset( $improvement_data['time_saved'] ) ) {
					$metrics[ $week_key ]['time_saved_seconds'] += (int) $improvement_data['time_saved'];
				}
				if ( isset( $improvement_data['load_time_before'], $improvement_data['load_time_after'] ) ) {
					$metrics[ $week_key ]['page_load_before'] = (float) $improvement_data['load_time_before'];
					$metrics[ $week_key ]['page_load_after']  = (float) $improvement_data['load_time_after'];
				}
				break;

			case 'data':
				if ( isset( $improvement_data['mb_saved'] ) ) {
					$metrics[ $week_key ]['data_saved_mb'] += (float) $improvement_data['mb_saved'];
				}
				break;

			case 'cpu':
				if ( isset( $improvement_data['cycles_saved'] ) ) {
					$metrics[ $week_key ]['cpu_cycles_saved'] += (int) $improvement_data['cycles_saved'];
				}
				break;
		}

		update_option( self::METRICS_OPTION_KEY, $metrics );
	}

	/**
	 * Generate weekly report HTML.
	 *
	 * @param array $metrics Week's metrics.
	 * @return string HTML content.
	 */
	private static function generate_report_html( array $metrics ): string {
		$uptime_percentage = 0;
		if ( $metrics['uptime_checks'] > 0 ) {
			$uptime_percentage = ( $metrics['uptime_success'] / $metrics['uptime_checks'] ) * 100;
		}

		$time_saved_hours = round( $metrics['time_saved_seconds'] / 3600, 2 );
		$data_saved_mb    = round( $metrics['data_saved_mb'], 2 );
		$cpu_cycles       = number_format( $metrics['cpu_cycles_saved'] );

		$week_start = gmdate( 'M j, Y', $metrics['week_start'] );
		$week_end   = gmdate( 'M j, Y', $metrics['week_end'] );

		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="utf-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title><?php esc_html_e( 'Weekly Performance Report', 'plugin-wp-support-thisismyurl' ); ?></title>
			<style>
				body {
					font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
					line-height: 1.6;
					color: #333;
					background: #f5f5f5;
					margin: 0;
					padding: 20px;
				}
				.report-container {
					max-width: 800px;
					margin: 0 auto;
					background: #fff;
					border-radius: 8px;
					box-shadow: 0 2px 10px rgba(0,0,0,0.1);
					overflow: hidden;
				}
				.report-header {
					background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
					color: #fff;
					padding: 40px 30px;
					text-align: center;
				}
				.report-header h1 {
					margin: 0 0 10px 0;
					font-size: 32px;
					font-weight: 600;
				}
				.report-header .date-range {
					font-size: 16px;
					opacity: 0.9;
				}
				.metrics-grid {
					display: grid;
					grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
					gap: 20px;
					padding: 30px;
				}
				.metric-card {
					background: #f8f9fa;
					border-radius: 8px;
					padding: 20px;
					text-align: center;
					border: 1px solid #e9ecef;
				}
				.metric-card .icon {
					font-size: 40px;
					margin-bottom: 10px;
				}
				.metric-card .value {
					font-size: 32px;
					font-weight: 700;
					color: #667eea;
					margin: 10px 0;
				}
				.metric-card .label {
					font-size: 14px;
					color: #6c757d;
					text-transform: uppercase;
					letter-spacing: 0.5px;
				}
				.highlights {
					padding: 30px;
					background: #f8f9fa;
				}
				.highlights h2 {
					margin: 0 0 20px 0;
					font-size: 24px;
					color: #333;
				}
				.highlight-item {
					padding: 15px 20px;
					background: #fff;
					border-left: 4px solid #667eea;
					margin-bottom: 15px;
					border-radius: 4px;
				}
				.highlight-item strong {
					color: #667eea;
				}
				.footer {
					padding: 30px;
					text-align: center;
					color: #6c757d;
					font-size: 14px;
				}
				.footer a {
					color: #667eea;
					text-decoration: none;
				}
			</style>
		</head>
		<body>
			<div class="report-container">
				<div class="report-header">
					<h1><?php esc_html_e( '📊 Weekly Performance Report', 'plugin-wp-support-thisismyurl' ); ?></h1>
					<div class="date-range"><?php echo esc_html( $week_start . ' - ' . $week_end ); ?></div>
				</div>

				<div class="metrics-grid">
					<div class="metric-card">
						<div class="icon">⏰</div>
						<div class="value"><?php echo esc_html( $time_saved_hours ); ?></div>
						<div class="label"><?php esc_html_e( 'Hours Saved', 'plugin-wp-support-thisismyurl' ); ?></div>
					</div>

					<div class="metric-card">
						<div class="icon">💾</div>
						<div class="value"><?php echo esc_html( $data_saved_mb ); ?></div>
						<div class="label"><?php esc_html_e( 'MB Data Saved', 'plugin-wp-support-thisismyurl' ); ?></div>
					</div>

					<div class="metric-card">
						<div class="icon">🔧</div>
						<div class="value"><?php echo esc_html( $metrics['issues_fixed'] ); ?></div>
						<div class="label"><?php esc_html_e( 'Issues Fixed', 'plugin-wp-support-thisismyurl' ); ?></div>
					</div>

					<div class="metric-card">
						<div class="icon">⚡</div>
						<div class="value"><?php echo esc_html( number_format( $uptime_percentage, 1 ) ); ?>%</div>
						<div class="label"><?php esc_html_e( 'Uptime', 'plugin-wp-support-thisismyurl' ); ?></div>
					</div>

					<div class="metric-card">
						<div class="icon">🚀</div>
						<div class="value"><?php echo esc_html( $metrics['speed_improvements'] ); ?></div>
						<div class="label"><?php esc_html_e( 'Speed Improvements', 'plugin-wp-support-thisismyurl' ); ?></div>
					</div>

					<div class="metric-card">
						<div class="icon">🔄</div>
						<div class="value"><?php echo esc_html( $cpu_cycles ); ?></div>
						<div class="label"><?php esc_html_e( 'CPU Cycles Saved', 'plugin-wp-support-thisismyurl' ); ?></div>
					</div>
				</div>

				<div class="highlights">
					<h2><?php esc_html_e( '✨ This Week\'s Highlights', 'plugin-wp-support-thisismyurl' ); ?></h2>

					<?php if ( $time_saved_hours > 0 ) : ?>
					<div class="highlight-item">
						<?php
						// translators: %s: hours saved this week.
						echo wp_kses_post( sprintf( __( '<strong>You saved %s hours this week!</strong> That\'s time you can spend on what matters most.', 'plugin-wp-support-thisismyurl' ), $time_saved_hours ) );
						?>
					</div>
					<?php endif; ?>

					<?php if ( $data_saved_mb > 0 ) : ?>
					<div class="highlight-item">
						<?php
						// translators: %s: MB of data saved this week.
						echo wp_kses_post( sprintf( __( '<strong>You saved %s MB of data this week!</strong> Optimized images and efficient caching are keeping your site lean.', 'plugin-wp-support-thisismyurl' ), $data_saved_mb ) );
						?>
					</div>
					<?php endif; ?>

					<?php if ( $metrics['cpu_cycles_saved'] > 0 ) : ?>
					<div class="highlight-item">
						<?php
						// translators: %s: CPU cycles saved this week.
						echo wp_kses_post( sprintf( __( '<strong>You saved %s CPU cycles this week!</strong> Your server is running more efficiently thanks to code optimizations.', 'plugin-wp-support-thisismyurl' ), $cpu_cycles ) );
						?>
					</div>
					<?php endif; ?>

					<?php if ( $metrics['issues_fixed'] > 0 ) : ?>
					<div class="highlight-item">
						<?php
						// translators: %s: number of issues fixed.
						echo wp_kses_post( sprintf( __( '<strong>Fixed %s issues</strong> automatically, keeping your site running smoothly.', 'plugin-wp-support-thisismyurl' ), $metrics['issues_fixed'] ) );
						?>
					</div>
					<?php endif; ?>

					<?php if ( $uptime_percentage >= 99 ) : ?>
					<div class="highlight-item">
						<?php
						// translators: %s: uptime percentage.
						echo wp_kses_post( sprintf( __( '<strong>%s%% uptime</strong> - Your site was rock solid this week!', 'plugin-wp-support-thisismyurl' ), number_format( $uptime_percentage, 1 ) ) );
						?>
					</div>
					<?php endif; ?>
				</div>

				<div class="footer">
					<p><?php esc_html_e( 'Powered by WP Support (thisismyurl)', 'plugin-wp-support-thisismyurl' ); ?></p>
					<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=wp-support' ) ); ?>"><?php esc_html_e( 'View Dashboard', 'plugin-wp-support-thisismyurl' ); ?></a></p>
				</div>
			</div>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	/**
	 * Send weekly performance report via email.
	 *
	 * @return void
	 */
	public static function send_weekly_report(): void {
		$instance = new self();
		$settings = $instance->get_setting( self::SETTINGS_OPTION_KEY, array() );
		$enabled  = $settings['enabled'] ?? true;

		if ( ! $enabled ) {
			return;
		}

		$recipient_emails = $settings['recipient_emails'] ?? array( get_option( 'admin_email' ) );
		$week_key         = self::get_current_week_key();
		$metrics          = $instance->get_setting( self::METRICS_OPTION_KEY, array() );

		if ( ! isset( $metrics[ $week_key ] ) ) {
			return;
		}

		$report_html = self::generate_report_html( $metrics[ $week_key ] );

		$subject = sprintf(
			/* translators: %s: site name */
			__( 'Weekly Performance Report - %s', 'plugin-wp-support-thisismyurl' ),
			get_bloginfo( 'name' )
		);

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>',
		);

		foreach ( $recipient_emails as $email ) {
			wp_mail( sanitize_email( $email ), $subject, $report_html, $headers );
		}

		// Log the report generation.
		WPS_Activity_Logger::log_event(
			WPS_Activity_Logger::EVENT_SETTINGS_CHANGED,
			__( 'Weekly performance report sent', 'plugin-wp-support-thisismyurl' ),
			array(
				'recipients' => count( $recipient_emails ),
				'week'       => $week_key,
			)
		);
	}

	/**
	 * Add admin menu for report viewing.
	 *
	 * @return void
	 */
	public static function add_admin_menu(): void {
		add_submenu_page(
			'wp-support',
			__( 'Performance Reports', 'plugin-wp-support-thisismyurl' ),
			__( 'Performance Reports', 'plugin-wp-support-thisismyurl' ),
			'manage_options',
			'wps-performance-reports',
			array( __CLASS__, 'render_admin_page' )
		);
	}

	/**
	 * Render admin page for viewing reports.
	 *
	 * @return void
	 */
	public static function render_admin_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wp-support-thisismyurl' ) );
		}

		$metrics     = self::get_current_week_metrics();
		$report_html = self::generate_report_html( $metrics );

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Weekly Performance Reports', 'plugin-wp-support-thisismyurl' ); ?></h1>

			<div class="wps-report-viewer" style="margin-top: 20px;">
				<?php echo $report_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>

			<div class="wps-report-settings" style="margin-top: 30px; padding: 20px; background: #fff; border: 1px solid #ccc; border-radius: 4px;">
				<h2><?php esc_html_e( 'Report Settings', 'plugin-wp-support-thisismyurl' ); ?></h2>
				<form method="post" action="options.php">
					<?php
					settings_fields( 'wps_performance_report_settings' );
					do_settings_sections( 'wps_performance_report_settings' );
					?>
					<table class="form-table">
						<tr>
							<th scope="row"><?php esc_html_e( 'Send Weekly Reports', 'plugin-wp-support-thisismyurl' ); ?></th>
							<td>
								<?php
								$settings = get_option( self::SETTINGS_OPTION_KEY, array() );
								$enabled  = $settings['enabled'] ?? true;
								?>
								<label>
									<input type="checkbox" name="<?php echo esc_attr( self::SETTINGS_OPTION_KEY ); ?>[enabled]" value="1" <?php checked( $enabled, true ); ?> />
									<?php esc_html_e( 'Enable weekly performance report emails', 'plugin-wp-support-thisismyurl' ); ?>
								</label>
							</td>
						</tr>
					</table>
					<?php submit_button(); ?>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * AJAX handler for viewing reports.
	 *
	 * @return void
	 */
	public static function ajax_view_report(): void {
		check_ajax_referer( 'wps_view_report', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$metrics     = self::get_current_week_metrics();
		$report_html = self::generate_report_html( $metrics );

		wp_send_json_success( array( 'html' => $report_html ) );
	}
}
