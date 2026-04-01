<?php
/**
 * Email Queue Health Diagnostic
 *
 * Checks if the email queue is healthy and processing emails properly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email Queue Health Diagnostic Class
 *
 * Monitors the email queue for stuck emails or processing delays. When emails
 * get stuck in the queue, customers don't receive confirmations and admins
 * miss important notifications.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Email_Queue_Health extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-queue-health';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email Queue Health';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Monitors email queue for stuck or delayed messages';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email';

	/**
	 * Run the email queue health diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if queue issues detected, null otherwise.
	 */
	public static function check() {
		// Check for email queue plugins.
		$queue_plugins = array(
			'wp-mail-queue/wp-mail-queue.php'     => 'WP Mail Queue',
			'wp-mail-smtp/wp_mail_smtp.php'       => 'WP Mail SMTP',
			'post-smtp/postman-smtp.php'          => 'Post SMTP',
		);

		$queue_enabled = false;
		$queue_plugin  = '';

		foreach ( $queue_plugins as $plugin_path => $name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$queue_enabled = true;
				$queue_plugin  = $name;
				break;
			}
		}

		// Check for WooCommerce email queue.
		if ( ! $queue_enabled && class_exists( 'WooCommerce' ) ) {
			if ( function_exists( 'wc_get_container' ) ) {
				$queue_enabled = true;
				$queue_plugin  = 'WooCommerce Action Scheduler';
			}
		}

		// Check WordPress cron for scheduled emails.
		if ( ! $queue_enabled ) {
			$cron_array = _get_cron_array();
			$has_email_cron = false;

			if ( ! empty( $cron_array ) ) {
				foreach ( $cron_array as $timestamp => $cron ) {
					foreach ( $cron as $hook => $dings ) {
						if ( false !== strpos( $hook, 'mail' ) || false !== strpos( $hook, 'email' ) ) {
							$has_email_cron = true;
							break 2;
						}
					}
				}
			}

			if ( $has_email_cron ) {
				$queue_enabled = true;
				$queue_plugin  = 'WordPress Cron';
			}
		}

		if ( ! $queue_enabled ) {
			// No queue system detected - this is actually okay for most sites.
			return array(
				'id'           => self::$slug . '-no-queue',
				'title'        => __( 'No Email Queue System', 'wpshadow' ),
				'description'  => __( 'Your site sends emails immediately instead of using a queue system (like dropping letters in the mailbox one at a time instead of batching them). This is fine for small sites, but adding a queue system can help larger sites avoid overwhelming email servers or hitting rate limits.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/email-queue?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'queue_enabled'        => false,
					'recommended_plugins'  => array_values( $queue_plugins ),
				),
			);
		}

		// Queue is enabled - check for stuck emails.
		global $wpdb;

		// Check WooCommerce Action Scheduler if available.
		if ( class_exists( 'WooCommerce' ) && function_exists( 'as_get_scheduled_actions' ) ) {
			// Check for failed email actions.
			$failed_actions = as_get_scheduled_actions(
				array(
					'status' => 'failed',
					'hook'   => 'woocommerce_send_queued_transactional_email',
					'per_page' => 10,
				)
			);

			if ( ! empty( $failed_actions ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						/* translators: %d: number of failed emails */
						__( 'Your email queue has %d failed emails that couldn\'t be sent (like letters returned to sender). This could mean customers aren\'t receiving order confirmations. Check your SMTP settings and email logs to identify the problem.', 'wpshadow' ),
						count( $failed_actions )
					),
					'severity'     => 'high',
					'threat_level' => 70,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/email-queue-troubleshooting?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'context'      => array(
						'queue_plugin'   => $queue_plugin,
						'failed_count'   => count( $failed_actions ),
						'queue_enabled'  => true,
					),
				);
			}

			// Check for very old pending actions (stuck in queue).
			$old_pending = as_get_scheduled_actions(
				array(
					'status' => 'pending',
					'hook'   => 'woocommerce_send_queued_transactional_email',
					'date'   => strtotime( '-1 hour' ),
					'per_page' => 10,
				)
			);

			if ( ! empty( $old_pending ) ) {
				return array(
					'id'           => self::$slug . '-stuck',
					'title'        => __( 'Stuck Emails in Queue', 'wpshadow' ),
					'description'  => sprintf(
						/* translators: %d: number of stuck emails */
						__( 'Your email queue has %d emails waiting to send for over an hour (like mail stuck in an outbox). This suggests the queue processor isn\'t running properly. Check that WordPress cron is working correctly.', 'wpshadow' ),
						count( $old_pending )
					),
					'severity'     => 'medium',
					'threat_level' => 60,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/email-queue-troubleshooting?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'context'      => array(
						'queue_plugin'   => $queue_plugin,
						'stuck_count'    => count( $old_pending ),
						'queue_enabled'  => true,
					),
				);
			}
		}

		// Check WordPress cron for stuck email tasks.
		$cron_array = _get_cron_array();
		$overdue_email_tasks = 0;

		if ( ! empty( $cron_array ) ) {
			$current_time = time();
			foreach ( $cron_array as $timestamp => $cron ) {
				if ( $timestamp < ( $current_time - 3600 ) ) { // More than 1 hour overdue.
					foreach ( $cron as $hook => $dings ) {
						if ( false !== strpos( $hook, 'mail' ) || false !== strpos( $hook, 'email' ) ) {
							$overdue_email_tasks += count( $dings );
						}
					}
				}
			}
		}

		if ( $overdue_email_tasks > 5 ) {
			return array(
				'id'           => self::$slug . '-cron-stuck',
				'title'        => __( 'Overdue Email Cron Tasks', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %d: number of overdue tasks */
					__( 'Your site has %d overdue email cron tasks (like scheduled mail pickups that were missed). This suggests WordPress cron isn\'t running properly. Consider using a real cron job instead of WP-Cron for better reliability.', 'wpshadow' ),
					$overdue_email_tasks
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wordpress-cron-troubleshooting?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'overdue_tasks' => $overdue_email_tasks,
					'queue_via'     => 'WordPress Cron',
				),
			);
		}

		return null; // Email queue is healthy.
	}
}
