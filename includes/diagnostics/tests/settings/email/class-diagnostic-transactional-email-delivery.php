<?php
/**
 * Transactional Email Delivery Rate Diagnostic
 *
 * Checks if transactional emails (order confirmations, password resets) are being delivered successfully.
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
 * Transactional Email Delivery Rate Diagnostic Class
 *
 * Monitors the success rate of critical transactional emails like order
 * confirmations, password resets, and account notifications.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Transactional_Email_Delivery extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'transactional-email-delivery';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Transactional Email Delivery Rate';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Monitors delivery success rate for critical transactional emails';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email';

	/**
	 * Run the transactional email delivery diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if delivery issues detected, null otherwise.
	 */
	public static function check() {
		// Check if email logging is enabled.
		$email_log_plugins = array(
			'wp-mail-logging/wp-mail-logging.php' => 'WP Mail Logging',
			'wp-mail-smtp/wp_mail_smtp.php'       => 'WP Mail SMTP',
			'post-smtp/postman-smtp.php'          => 'Post SMTP',
			'email-log/email-log.php'             => 'Email Log',
		);

		$logging_enabled = false;
		$logging_plugin  = '';

		foreach ( $email_log_plugins as $plugin_path => $name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$logging_enabled = true;
				$logging_plugin  = $name;
				break;
			}
		}

		if ( ! $logging_enabled ) {
			return array(
				'id'           => self::$slug . '-no-logging',
				'title'        => __( 'Email Logging Not Enabled', 'wpshadow' ),
				'description'  => __( 'Adding email logging helps you track which emails are sent successfully and which fail (like keeping a delivery receipt for every message). This is especially important for order confirmations and password resets. Popular plugins include WP Mail Logging or WP Mail SMTP.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/email-logging',
				'context'      => array(
					'logging_enabled'      => false,
					'recommended_plugins'  => array_values( $email_log_plugins ),
				),
			);
		}

		// If logging is enabled, check recent delivery rates.
		$recent_failures = get_transient( 'wpshadow_email_recent_failures' );

		if ( false === $recent_failures ) {
			// Try to detect failures through WP Mail SMTP if active.
			if ( is_plugin_active( 'wp-mail-smtp/wp_mail_smtp.php' ) && function_exists( 'wp_mail_smtp' ) ) {
				// Check WP Mail SMTP logs.
				$smtp_logs = get_option( 'wp_mail_smtp_debug', array() );
				if ( ! empty( $smtp_logs ) && is_array( $smtp_logs ) ) {
					$failed_count = 0;
					$total_count  = count( $smtp_logs );
					foreach ( $smtp_logs as $log_entry ) {
						if ( isset( $log_entry['status'] ) && 'failed' === $log_entry['status'] ) {
							$failed_count++;
						}
					}

					if ( $total_count > 0 && ( $failed_count / $total_count ) > 0.1 ) {
						// More than 10% failure rate.
						return array(
							'id'           => self::$slug,
							'title'        => self::$title,
							'description'  => sprintf(
								/* translators: 1: failure percentage, 2: failed count, 3: total count */
								__( 'Your email delivery rate is lower than expected (%1$s%% of emails failing). Out of %3$d recent emails, %2$d failed to send. This could mean customers aren\'t receiving order confirmations or password reset emails.', 'wpshadow' ),
								number_format( ( $failed_count / $total_count ) * 100, 1 ),
								$failed_count,
								$total_count
							),
							'severity'     => 'high',
							'threat_level' => 70,
							'auto_fixable' => false,
							'kb_link'      => 'https://wpshadow.com/kb/email-delivery-troubleshooting',
							'context'      => array(
								'failed_count'  => $failed_count,
								'total_count'   => $total_count,
								'failure_rate'  => ( $failed_count / $total_count ) * 100,
								'logging_via'   => $logging_plugin,
							),
						);
					}
				}
			}
		}

		// Check for bounced email reports.
		$bounce_rate = get_option( 'wpshadow_email_bounce_rate', 0 );
		if ( $bounce_rate > 5 ) {
			return array(
				'id'           => self::$slug . '-high-bounce',
				'title'        => __( 'High Email Bounce Rate', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: bounce rate percentage */
					__( 'Your email bounce rate is %s%% (emails being rejected by recipient servers). A high bounce rate can damage your sender reputation and cause future emails to be blocked. This often happens with invalid email addresses or spam filtering.', 'wpshadow' ),
					$bounce_rate
				),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/email-bounce-rate',
				'context'      => array(
					'bounce_rate'  => $bounce_rate,
					'threshold'    => 5,
				),
			);
		}

		return null; // Email delivery appears healthy.
	}
}
