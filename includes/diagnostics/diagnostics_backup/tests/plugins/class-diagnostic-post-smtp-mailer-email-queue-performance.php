<?php
/**
 * Post Smtp Mailer Email Queue Performance Diagnostic
 *
 * Post Smtp Mailer Email Queue Performance issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1461.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Smtp Mailer Email Queue Performance Diagnostic Class
 *
 * @since 1.1461.0000
 */
class Diagnostic_PostSmtpMailerEmailQueuePerformance extends Diagnostic_Base {

	protected static $slug = 'post-smtp-mailer-email-queue-performance';
	protected static $title = 'Post Smtp Mailer Email Queue Performance';
	protected static $description = 'Post Smtp Mailer Email Queue Performance issue found';
	protected static $family = 'performance';

	public static function check() {
		// Check for Post SMTP
		if ( ! defined( 'POST_SMTP_VER' ) && ! class_exists( 'PostmanOptions' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Email queue size
		$queue_table = $wpdb->prefix . 'post_smtp_email_log';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $queue_table ) ) === $queue_table ) {
			$queue_size = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$queue_table} WHERE status = %s",
					'pending'
				)
			);
			
			if ( $queue_size > 100 ) {
				$issues[] = sprintf( __( '%d emails in queue (backlog)', 'wpshadow' ), $queue_size );
			}
			
			// Check 2: Failed emails
			$failed_count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$queue_table} WHERE status = %s",
					'failed'
				)
			);
			
			if ( $failed_count > 50 ) {
				$issues[] = sprintf( __( '%d failed emails (delivery issues)', 'wpshadow' ), $failed_count );
			}
		}
		
		// Check 3: Sending rate
		$send_rate = get_option( 'postman_send_rate', 10 );
		if ( $send_rate > 50 ) {
			$issues[] = sprintf( __( 'Send rate: %d emails/min (SMTP throttling risk)', 'wpshadow' ), $send_rate );
		}
		
		// Check 4: Queue processing
		$queue_enabled = get_option( 'postman_enable_queue', 'yes' );
		if ( 'no' === $queue_enabled ) {
			$issues[] = __( 'Queue disabled (synchronous sending, slow page loads)', 'wpshadow' );
		}
		
		// Check 5: Retry attempts
		$max_retries = get_option( 'postman_max_retries', 3 );
		if ( $max_retries < 2 ) {
			$issues[] = sprintf( __( 'Max retries: %d (emails may be lost)', 'wpshadow' ), $max_retries );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 55;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 68;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 62;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of email queue issues */
				__( 'Post SMTP email queue has %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/post-smtp-mailer-email-queue-performance',
		);
	}
}
