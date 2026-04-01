<?php
/**
 * Email Logging Enabled Diagnostic
 *
 * Checks if email logging is enabled for troubleshooting and audit purposes.
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
 * Email Logging Enabled Diagnostic Class
 *
 * Verifies that email logging is active for troubleshooting purposes.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Email_Logging_Enabled extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-logging-enabled';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email Logging Enabled';

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
	protected static $family = 'email-deliverability';

	/**
	 * Run the email logging diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if logging is not enabled, null otherwise.
	 */
	public static function check() {
		$logging_info = self::get_email_logging_status();

		if ( ! $logging_info['enabled'] ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Email logging is not enabled. Without logging, troubleshooting email delivery issues will be difficult.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/email-logging?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		// Check if retention policy is set.
		if ( $logging_info['enabled'] && empty( $logging_info['retention_days'] ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Email logging is enabled but no retention policy is set. Logs may grow indefinitely.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/email-log-retention?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'        => array(
					'logging_method' => $logging_info['method'],
				),
			);
		}

		return null;
	}

	/**
	 * Get email logging status from various sources.
	 *
	 * @since 0.6093.1200
	 * @return array Logging status information.
	 */
	private static function get_email_logging_status(): array {
		$status = array(
			'enabled'        => false,
			'method'         => '',
			'retention_days' => 0,
		);

		// Check WP Mail Logging plugin.
		if ( class_exists( 'WP_Mail_Logging' ) ) {
			$status['enabled'] = true;
			$status['method'] = 'WP Mail Logging';
			return $status;
		}

		// Check Email Log plugin.
		if ( function_exists( 'email_log_init' ) ) {
			$status['enabled'] = true;
			$status['method'] = 'Email Log';
			return $status;
		}

		// Check WP Mail SMTP plugin logging.
		$wp_mail_smtp = get_option( 'wp_mail_smtp' );
		if ( ! empty( $wp_mail_smtp['general']['email_log_enabled'] ) ) {
			$status['enabled'] = true;
			$status['method'] = 'WP Mail SMTP';
			if ( ! empty( $wp_mail_smtp['general']['email_log_retention'] ) ) {
				$status['retention_days'] = (int) $wp_mail_smtp['general']['email_log_retention'];
			}
			return $status;
		}

		// Check Post SMTP plugin logging.
		$post_smtp = get_option( 'postman_options' );
		if ( ! empty( $post_smtp['log_level'] ) && 'OFF' !== $post_smtp['log_level'] ) {
			$status['enabled'] = true;
			$status['method'] = 'Post SMTP';
			return $status;
		}

		// Check if WPShadow Activity Logger is tracking emails.
		if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			$activity_log = get_option( \WPShadow\Core\Activity_Logger::OPTION_NAME, array() );

			if ( is_array( $activity_log ) ) {
				foreach ( $activity_log as $entry ) {
					$action = isset( $entry['action'] ) ? (string) $entry['action'] : '';

					if ( in_array( $action, array( 'email_sent', 'email_bounced', 'email_test_success' ), true ) ) {
						$status['enabled'] = true;
						$status['method'] = 'WPShadow Activity Logger';
						return $status;
					}
				}
			}
		}

		return $status;
	}
}
