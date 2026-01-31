<?php
/**
 * Happyforms Email Deliverability Diagnostic
 *
 * Happyforms Email Deliverability issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1210.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Happyforms Email Deliverability Diagnostic Class
 *
 * @since 1.1210.0000
 */
class Diagnostic_HappyformsEmailDeliverability extends Diagnostic_Base {

	protected static $slug = 'happyforms-email-deliverability';
	protected static $title = 'Happyforms Email Deliverability';
	protected static $description = 'Happyforms Email Deliverability issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'happyforms_get_form_controller' ) ) {
			return null;
		}
		
		$issues = array();

		// Check 1: Verify SMTP configuration
		$smtp_enabled = get_option( 'happyforms_smtp_enabled', false );
		if ( ! $smtp_enabled ) {
			$issues[] = __( 'SMTP not configured for reliable email delivery', 'wpshadow' );
		}

		// Check 2: Check email logging enabled
		$email_logging = get_option( 'happyforms_email_logging', false );
		if ( ! $email_logging ) {
			$issues[] = __( 'Email logging not enabled for delivery tracking', 'wpshadow' );
		}

		// Check 3: Verify delivery failure notifications
		$failure_notifications = get_option( 'happyforms_delivery_failure_notifications', false );
		if ( ! $failure_notifications ) {
			$issues[] = __( 'Delivery failure notifications not configured', 'wpshadow' );
		}

		// Check 4: Check SPF and DKIM configuration
		$spf_dkim_configured = get_option( 'happyforms_spf_dkim_configured', false );
		if ( ! $spf_dkim_configured ) {
			$issues[] = __( 'SPF/DKIM not configured for email authentication', 'wpshadow' );
		}

		// Check 5: Verify from email address configuration
		$from_email = get_option( 'happyforms_from_email', '' );
		if ( empty( $from_email ) || strpos( $from_email, 'wordpress@' ) === 0 ) {
			$issues[] = __( 'Default or invalid from email address configured', 'wpshadow' );
		}

		// Check 6: Check bounce handling configuration
		$bounce_handling = get_option( 'happyforms_bounce_handling', false );
		if ( ! $bounce_handling ) {
			$issues[] = __( 'Email bounce handling not configured', 'wpshadow' );
		}
		return null;
	}
}
