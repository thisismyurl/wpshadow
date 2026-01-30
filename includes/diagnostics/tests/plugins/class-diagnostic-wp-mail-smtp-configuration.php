<?php
/**
 * Wp Mail Smtp Configuration Diagnostic
 *
 * Wp Mail Smtp Configuration issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1457.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Mail Smtp Configuration Diagnostic Class
 *
 * @since 1.1457.0000
 */
class Diagnostic_WpMailSmtpConfiguration extends Diagnostic_Base {

	protected static $slug = 'wp-mail-smtp-configuration';
	protected static $title = 'Wp Mail Smtp Configuration';
	protected static $description = 'Wp Mail Smtp Configuration issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'WPMS_PLUGIN_VER' ) && ! class_exists( 'WPMailSMTP\Core' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: SMTP configuration
		$smtp_settings = get_option( 'wp_mail_smtp', array() );
		if ( empty( $smtp_settings ) || ! isset( $smtp_settings['mail']['mailer'] ) ) {
			$issues[] = 'SMTP mailer not configured';
		}
		
		// Check 2: Authentication credentials
		if ( ! empty( $smtp_settings ) && isset( $smtp_settings['mail']['mailer'] ) && 'smtp' === $smtp_settings['mail']['mailer'] ) {
			if ( empty( $smtp_settings['smtp']['user'] ) || empty( $smtp_settings['smtp']['pass'] ) ) {
				$issues[] = 'SMTP authentication credentials not set';
			}
		}
		
		// Check 3: Encryption method
		if ( ! empty( $smtp_settings ) && isset( $smtp_settings['smtp']['encryption'] ) ) {
			if ( 'none' === $smtp_settings['smtp']['encryption'] ) {
				$issues[] = 'SMTP encryption disabled (emails sent insecurely)';
			}
		} else {
			$issues[] = 'SMTP encryption not configured';
		}
		
		// Check 4: From email address
		$from_email = isset( $smtp_settings['mail']['from_email'] ) ? $smtp_settings['mail']['from_email'] : '';
		if ( empty( $from_email ) ) {
			$issues[] = 'from email address not configured';
		} elseif ( ! is_email( $from_email ) ) {
			$issues[] = 'invalid from email address format';
		}
		
		// Check 5: Email logging
		$logging_enabled = get_option( 'wp_mail_smtp_logging', '0' );
		if ( '0' === $logging_enabled ) {
			$issues[] = 'email logging disabled (cannot troubleshoot failures)';
		} else {
			global $wpdb;
			$failed_emails = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->prefix}wpms_logs WHERE status = %s",
					'failed'
				)
			);
			if ( $failed_emails > 10 ) {
				$issues[] = "{$failed_emails} failed email deliveries logged";
			}
		}
		
		// Check 6: Rate limiting or queuing
		if ( ! empty( $smtp_settings ) ) {
			$rate_limit = get_option( 'wp_mail_smtp_rate_limit', 0 );
			if ( empty( $rate_limit ) ) {
				$issues[] = 'no email rate limiting (may hit provider limits)';
			}
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'WP Mail SMTP configuration issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp-mail-smtp-configuration',
			);
		}
		
		return null;
	}
}
