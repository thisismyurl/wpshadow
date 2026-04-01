<?php
/**
 * Email Logging Diagnostic
 *
 * Checks if email logging is enabled for troubleshooting and monitoring.
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
 * Email Logging Diagnostic Class
 *
 * Verifies that email logging is enabled. Without logs, it's impossible to
 * troubleshoot why emails aren't being delivered (like trying to track a
 * package without a tracking number).
 *
 * @since 0.6093.1200
 */
class Diagnostic_Email_Logging extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-logging';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email Logging';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if email logging is enabled for troubleshooting';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email';

	/**
	 * Run the email logging diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if logging issues detected, null otherwise.
	 */
	public static function check() {
		// Check for email logging plugins.
		$logging_plugins = array(
			'wp-mail-logging/wp-mail-logging.php' => 'WP Mail Logging',
			'wp-mail-smtp/wp_mail_smtp.php'       => 'WP Mail SMTP',
			'post-smtp/postman-smtp.php'          => 'Post SMTP',
			'email-log/email-log.php'             => 'Email Log',
			'check-email/check-email.php'         => 'Check & Log Email',
		);

		$logging_enabled = false;
		$logging_plugin  = '';
		$logging_details = array();

		foreach ( $logging_plugins as $plugin_path => $name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$logging_enabled = true;
				$logging_plugin  = $name;

				// Check if logging is actually enabled (some plugins have toggles).
				if ( 'wp-mail-smtp/wp_mail_smtp.php' === $plugin_path ) {
					if ( function_exists( 'wp_mail_smtp' ) ) {
						$options = get_option( 'wp_mail_smtp', array() );
						if ( isset( $options['mail']['log_email_content'] ) && $options['mail']['log_email_content'] ) {
							$logging_details['content_logging'] = true;
						}
					}
				}
				break;
			}
		}

		// Check for custom logging via hooks.
		if ( ! $logging_enabled ) {
			if ( has_action( 'wp_mail_succeeded' ) || has_action( 'wp_mail_failed' ) ) {
				$logging_enabled = true;
				$logging_plugin  = __( 'Custom email logging hooks', 'wpshadow' );
			}
		}

		// Check for debug logging.
		if ( ! $logging_enabled ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				// Debug logging is on, but emails aren't specifically logged.
				return array(
					'id'           => self::$slug . '-debug-only',
					'title'        => __( 'Limited Email Logging', 'wpshadow' ),
					'description'  => __( 'Your site has debug logging enabled, but this doesn\'t capture full email details (like writing "sent mail" in a notebook without keeping copies). Adding dedicated email logging helps you see exactly what was sent, when, and to whom. This is especially helpful when troubleshooting delivery issues.', 'wpshadow' ),
					'severity'     => 'low',
					'threat_level' => 35,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/email-logging?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'context'      => array(
						'logging_type'         => 'debug',
						'recommended_plugins'  => array_values( $logging_plugins ),
					),
				);
			}
		}

		if ( ! $logging_enabled ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Adding email logging helps you track every email your site sends (like keeping delivery receipts). When customers say "I never received my order confirmation," you can check the logs to see if it was sent, when, and to which address. This makes troubleshooting much easier.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/email-logging?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'logging_enabled'      => false,
					'recommended_plugins'  => array_values( $logging_plugins ),
				),
			);
		}

		// Logging is enabled - check log retention and size.
		$warnings = array();

		// Check for very old logs (may indicate logs aren't being cleared).
		global $wpdb;

		// Check WP Mail Logging plugin tables if available.
		$log_table = $wpdb->prefix . 'mail_log';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $log_table ) ) === $log_table ) {
			$oldest_log = $wpdb->get_var( "SELECT MIN(sent_date) FROM {$log_table}" );
			if ( $oldest_log ) {
				$oldest_timestamp = strtotime( $oldest_log );
				$days_old = ( time() - $oldest_timestamp ) / DAY_IN_SECONDS;

				if ( $days_old > 90 ) {
					$warnings[] = sprintf(
						/* translators: %d: number of days */
						__( 'Email logs haven\'t been cleaned up in %d days - consider enabling automatic cleanup', 'wpshadow' ),
						(int) $days_old
					);
				}
			}

			// Check log size.
			$log_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$log_table}" );
			if ( $log_count > 10000 ) {
				$warnings[] = sprintf(
					/* translators: %d: number of log entries */
					__( 'Email log has %s entries - consider reducing retention period to save database space', 'wpshadow' ),
					number_format_i18n( $log_count )
				);
			}
		}

		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug . '-warnings',
				'title'        => __( 'Email Logging Warnings', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: logging plugin name */
					__( 'Email logging is enabled via %s, but there are some optimization suggestions.', 'wpshadow' ),
					$logging_plugin
				),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/email-logging?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'logging_enabled' => true,
					'logging_via'     => $logging_plugin,
					'warnings'        => $warnings,
				),
			);
		}

		return null; // Email logging is healthy.
	}
}
