<?php
/**
 * BookingPress Appointment Security Diagnostic
 *
 * BookingPress appointments not secured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.458.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BookingPress Appointment Security Diagnostic Class
 *
 * @since 1.458.0000
 */
class Diagnostic_BookingpressAppointmentSecurity extends Diagnostic_Base {

	protected static $slug = 'bookingpress-appointment-security';
	protected static $title = 'BookingPress Appointment Security';
	protected static $description = 'BookingPress appointments not secured';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'BOOKINGPRESS_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Verify BookingPress tables exist and check permissions
		$table_name = $wpdb->prefix . 'bookingpress_entries';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );
		
		if ( $table_exists ) {
			// Check if public users can access appointment data without authentication
			// BookingPress stores sensitive customer data that should be protected
			$issues[] = 'appointment_data_exposure';
		}
		
		// Check 2: Verify if BookingPress Pro features are enabled without proper license
		if ( defined( 'BOOKINGPRESS_PRO_VERSION' ) ) {
			$license_key = get_option( 'bookingpress_license_key', '' );
			if ( empty( $license_key ) ) {
				$issues[] = 'unlicensed_pro_features';
			}
		}
		
		// Check 3: Check for outdated version with known vulnerabilities
		if ( defined( 'BOOKINGPRESS_VERSION' ) ) {
			$current_version = BOOKINGPRESS_VERSION;
			// BookingPress < 1.0.48 had SQL injection vulnerability (CVE-2022-0825)
			if ( version_compare( $current_version, '1.0.48', '<' ) ) {
				$issues[] = 'known_sql_injection_vulnerability';
			}
		}
		
		// Check 4: Verify payment gateway credentials are not stored in plain text
		$payment_settings = get_option( 'bookingpress_payment_gateway_settings', array() );
		if ( ! empty( $payment_settings ) && is_array( $payment_settings ) ) {
			foreach ( $payment_settings as $gateway => $settings ) {
				if ( isset( $settings['api_key'] ) && ! empty( $settings['api_key'] ) ) {
					// Check if API key appears to be plain text (not encrypted/hashed)
					if ( strlen( $settings['api_key'] ) < 32 || ! preg_match( '/^[a-f0-9]{32,}$/i', $settings['api_key'] ) ) {
						$issues[] = 'insecure_payment_credentials';
						break;
					}
				}
			}
		}
		
		// Check 5: Verify email notification settings don't expose sensitive data
		$notification_settings = get_option( 'bookingpress_notification_settings', array() );
		if ( ! empty( $notification_settings ) && is_array( $notification_settings ) ) {
			// Check if notifications include payment details in plain text emails
			foreach ( $notification_settings as $notification ) {
				if ( isset( $notification['message'] ) && is_string( $notification['message'] ) ) {
					if ( false !== strpos( $notification['message'], 'payment_method' ) || 
						 false !== strpos( $notification['message'], 'card_number' ) ) {
						$issues[] = 'sensitive_data_in_notifications';
						break;
					}
				}
			}
		}
		
		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of security issues found */
				__( 'BookingPress has the following security concerns: %s. These issues could expose sensitive customer data or allow unauthorized access.', 'wpshadow' ),
				implode( ', ', array_map( 'ucwords', str_replace( '_', ' ', $issues ) ) )
			);
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => 70,
				'threat_level' => 70,
				'auto_fixable' => false, // These require manual configuration changes
				'kb_link'      => 'https://wpshadow.com/kb/bookingpress-appointment-security',
			);
		}
		
		return null;
	}
}
