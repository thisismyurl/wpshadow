<?php
/**
 * Wp Mail Smtp Email Log Storage Diagnostic
 *
 * Wp Mail Smtp Email Log Storage issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1458.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Mail Smtp Email Log Storage Diagnostic Class
 *
 * @since 1.1458.0000
 */
class Diagnostic_WpMailSmtpEmailLogStorage extends Diagnostic_Base {

	protected static $slug = 'wp-mail-smtp-email-log-storage';
	protected static $title = 'Wp Mail Smtp Email Log Storage';
	protected static $description = 'Wp Mail Smtp Email Log Storage issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'WPMS_PLUGIN_VER' ) ) {
			return null;
		}

		global $wpdb;
		$issues = array();

		// Check 1: Email logging enabled
		$log_enabled = get_option( 'wp_mail_smtp_email_log', 'no' );
		if ( 'no' === $log_enabled ) {
			return null;
		}

		// Check 2: Log table size
		$log_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}wpms_email_log"
		);

		if ( $log_count > 10000 ) {
			$issues[] = sprintf( __( '%d emails logged (database bloat)', 'wpshadow' ), number_format( $log_count ) );
		}

		// Check 3: Auto-delete
		$auto_delete = get_option( 'wp_mail_smtp_log_auto_delete', 'no' );
		if ( 'no' === $auto_delete ) {
			$issues[] = __( 'Logs never deleted (grows forever)', 'wpshadow' );
		}

		// Check 4: Retention period
		$retention_days = get_option( 'wp_mail_smtp_log_retention_days', 0 );
		if ( $retention_days === 0 || $retention_days > 90 ) {
			$issues[] = __( 'Long retention (privacy concern)', 'wpshadow' );
		}

		// Check 5: Log content
		$log_content = get_option( 'wp_mail_smtp_log_content', 'yes' );
		if ( 'yes' === $log_content ) {
			$issues[] = __( 'Email content logged (sensitive data)', 'wpshadow' );
		}

		// Check 6: Log attachments
		$log_attachments = get_option( 'wp_mail_smtp_log_attachments', 'yes' );
		if ( 'yes' === $log_attachments ) {
			$issues[] = __( 'Attachments logged (disk space)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'WP Mail SMTP log storage has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wp-mail-smtp-email-log-storage',
		);
	}
}
