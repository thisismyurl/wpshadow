<?php
/**
 * Caldera Forms Email Delivery Diagnostic
 *
 * Caldera Forms emails not sending.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.475.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Caldera Forms Email Delivery Diagnostic Class
 *
 * @since 1.475.0000
 */
class Diagnostic_CalderaFormsEmailDelivery extends Diagnostic_Base {

	protected static $slug = 'caldera-forms-email-delivery';
	protected static $title = 'Caldera Forms Email Delivery';
	protected static $description = 'Caldera Forms emails not sending';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'Caldera_Forms' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Email notification configured
		$forms = get_option( 'caldera_forms', array() );
		if ( ! empty( $forms ) && is_array( $forms ) ) {
			foreach ( $forms as $form ) {
				if ( isset( $form['mailer'] ) && empty( $form['mailer']['sender_email'] ) ) {
					$issues[] = 'form without sender email configured';
					break;
				}
			}
		}

		// Check 2: From email validation
		$default_from = get_option( 'caldera_forms_default_from_email', '' );
		if ( ! empty( $default_from ) && ! is_email( $default_from ) ) {
			$issues[] = 'invalid default from email address';
		}

		// Check 3: Email delivery errors
		$error_log = get_transient( 'caldera_forms_email_errors' );
		if ( ! empty( $error_log ) && is_array( $error_log ) ) {
			$error_count = count( $error_log );
			if ( $error_count > 5 ) {
				$issues[] = "{$error_count} recent email delivery failures";
			}
		}

		// Check 4: SMTP vs wp_mail
		$mailer_type = get_option( 'caldera_forms_mailer_type', 'wp_mail' );
		if ( 'wp_mail' === $mailer_type && ! function_exists( 'wp_mail' ) ) {
			$issues[] = 'wp_mail function not available';
		}

		// Check 5: Email queue processing
		$queue_enabled = get_option( 'caldera_forms_email_queue', '0' );
		if ( '1' === $queue_enabled ) {
			$queue_count = get_option( 'caldera_forms_queue_count', 0 );
			if ( $queue_count > 50 ) {
				$issues[] = "{$queue_count} emails pending in queue (delivery delays)";
			}
		}

		// Check 6: Email logging
		$logging = get_option( 'caldera_forms_email_logging', '0' );
		if ( '0' === $logging ) {
			$issues[] = 'email logging disabled (cannot troubleshoot delivery)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Caldera Forms email delivery issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/caldera-forms-email-delivery',
			);
		}

		return null;
	}
}
