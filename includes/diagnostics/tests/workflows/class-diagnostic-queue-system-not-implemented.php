<?php
/**
 * Queue System Not Implemented Diagnostic
 *
 * Checks queue system.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Queue_System_Not_Implemented Class
 *
 * Performs diagnostic check for Queue System Not Implemented.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Queue_System_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'queue-system-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Queue System Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks queue system';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for queue/async task plugins.
		$queue_plugins = array(
			'action-scheduler/action-scheduler.php'       => 'Action Scheduler',
			'woocommerce/woocommerce.php'                 => 'WooCommerce (includes Action Scheduler)',
			'wp-cron-control/wp-cron-control.php'         => 'WP Cron Control',
			'advanced-cron-manager/advanced-cron-manager.php' => 'Advanced Cron Manager',
			'wp-queue/wp-queue.php'                       => 'WP Queue',
		);

		$has_queue_system = false;
		foreach ( $queue_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_queue_system = true;
				break;
			}
		}

		// Check if Action Scheduler is loaded (even if not as standalone plugin).
		if ( class_exists( 'ActionScheduler' ) || function_exists( 'as_schedule_single_action' ) ) {
			$has_queue_system = true;
		}

		// Check for long-running operations that need queuing.
		$needs_queue = false;

		// Check for plugins that typically need background processing.
		$heavy_plugins = array(
			'woocommerce/woocommerce.php'          => 'WooCommerce (order processing)',
			'easy-digital-downloads/easy-digital-downloads.php' => 'Easy Digital Downloads',
			'memberpress/memberpress.php'          => 'MemberPress',
			'learndash/learndash.php'              => 'LearnDash',
			'gravityforms/gravityforms.php'        => 'Gravity Forms',
			'wpforms/wpforms.php'                  => 'WPForms',
			'mailchimp-for-wp/mailchimp-for-wp.php' => 'Mailchimp for WordPress',
		);

		foreach ( $heavy_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$needs_queue = true;
				break;
			}
		}

		// Check for scheduled cron jobs (indicator of background tasks).
		$cron_jobs = _get_cron_array();
		if ( is_array( $cron_jobs ) && count( $cron_jobs ) > 10 ) {
			$needs_queue = true;
		}

		// Only flag if site needs background processing but has no queue system.
		if ( $needs_queue && ! $has_queue_system ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Queue system not implemented. Long-running tasks (email sends, report generation, imports) run synchronously. Users wait for page loads. Timeouts occur. Bad UX.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/queue-system-not-implemented',
				'details'     => array(
					'has_queue_system' => $has_queue_system,
					'needs_queue'      => $needs_queue,
					'cron_job_count'   => is_array( $cron_jobs ) ? count( $cron_jobs ) : 0,
					'recommendation'    => __( 'Implement queue system for background tasks. Use Action Scheduler (WordPress standard), WP Cron, or custom queue. Move long operations to background: email sends, report generation, CSV imports, data syncing.', 'wpshadow' ),
					'performance_impact' => __( 'Without queue: User submits form, waits 45 seconds while emails send. Timeout occurs, form fails. With queue: User submits, sees success immediately. Emails send in background. 100x better UX.', 'wpshadow' ),
					'use_cases'        => array(
						__( 'Email campaigns: Send bulk emails in background', 'wpshadow' ),
						__( 'Report generation: Build PDF reports asynchronously', 'wpshadow' ),
						__( 'CSV imports: Process large file uploads in chunks', 'wpshadow' ),
						__( 'Image processing: Resize/optimize images after upload', 'wpshadow' ),
						__( 'API syncing: Push data to external services', 'wpshadow' ),
						__( 'Database maintenance: Clean expired data periodically', 'wpshadow' ),
					),
					'queue_systems'    => array(
						'Action Scheduler (WordPress standard, used by WooCommerce)',
						'WP Cron (built-in, but unreliable on low-traffic sites)',
						'WP Queue (dedicated queue plugin)',
						'WP Background Processing library',
						'Server-side queue (Redis, RabbitMQ, Beanstalkd)',
					),
				),
			);
		}

		return null;
	}
}
