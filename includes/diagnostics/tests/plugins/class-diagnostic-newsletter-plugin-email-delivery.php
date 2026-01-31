<?php
/**
 * Newsletter Plugin Email Delivery Diagnostic
 *
 * Newsletter Plugin Email Delivery configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.716.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Newsletter Plugin Email Delivery Diagnostic Class
 *
 * @since 1.716.0000
 */
class Diagnostic_NewsletterPluginEmailDelivery extends Diagnostic_Base {

	protected static $slug = 'newsletter-plugin-email-delivery';
	protected static $title = 'Newsletter Plugin Email Delivery';
	protected static $description = 'Newsletter Plugin Email Delivery configuration issues';
	protected static $family = 'functionality';

	public static function check() {
		// Check if Newsletter plugin is installed
		if ( ! class_exists( 'Newsletter' ) ) {
			return null;
		}

		global $wpdb;
		$issues = array();
		$threat_level = 0;

		// Check delivery method
		$delivery_method = get_option( 'newsletter_main', array() );
		$method = isset( $delivery_method['mail_method'] ) ? $delivery_method['mail_method'] : 'mail';
		if ( $method === 'mail' ) {
			$issues[] = 'using_php_mail';
			$threat_level += 15;
		}

		// Check send rate
		$max_emails = isset( $delivery_method['scheduler_max'] ) ? $delivery_method['scheduler_max'] : 0;
		if ( $max_emails < 10 ) {
			$issues[] = 'low_send_rate';
			$threat_level += 10;
		}

		// Check bounce management
		$bounce_email = isset( $delivery_method['return_path'] ) ? $delivery_method['return_path'] : '';
		if ( empty( $bounce_email ) ) {
			$issues[] = 'no_bounce_handling';
			$threat_level += 10;
		}

		// Check email queue
		$queue_table = $wpdb->prefix . 'newsletter_emails';
		$queue_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$queue_table} WHERE status = %s",
				'sending'
			)
		);
		if ( $queue_count > 1000 ) {
			$issues[] = 'large_email_queue';
			$threat_level += 15;
		}

		// Check failed emails
		$failed_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$queue_table} WHERE status = %s",
				'error'
			)
		);
		if ( $failed_count > 100 ) {
			$issues[] = 'high_failure_rate';
			$threat_level += 20;
		}

		// Check delivery logging
		$logging_enabled = isset( $delivery_method['log'] ) ? $delivery_method['log'] : 0;
		if ( ! $logging_enabled ) {
			$issues[] = 'delivery_logging_disabled';
			$threat_level += 5;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of email delivery issues */
				__( 'Newsletter plugin email delivery has problems: %s. This can cause emails to fail, land in spam, or not be delivered at all.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/newsletter-plugin-email-delivery',
			);
		}
		
		return null;
	}
}
