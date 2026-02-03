<?php
/**
 * Failed GDPR Export Email Delivery Diagnostic
 *
 * Tests whether personal data export email notifications are delivered successfully.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Privacy
 * @since      1.2034.1430
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Failed_GDPR_Export_Email_Delivery Class
 *
 * Verifies that GDPR export notification emails can be sent.
 *
 * @since 1.2034.1430
 */
class Diagnostic_Failed_GDPR_Export_Email_Delivery extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'failed-gdpr-export-email-delivery';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'GDPR Export Email Delivery';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if personal data export notification emails can be delivered';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2034.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// 1. Check if wp_mail function exists.
		if ( ! function_exists( 'wp_mail' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'WordPress email functionality is not available', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/gdpr-export-email-delivery',
			);
		}

		// 2. Check if admin email is set.
		$admin_email = get_option( 'admin_email' );
		if ( empty( $admin_email ) || ! is_email( $admin_email ) ) {
			$issues[] = __( 'Admin email is not configured or invalid', 'wpshadow' );
		}

		// 3. Check if site name is set (used in email subject).
		$site_name = get_bloginfo( 'name' );
		if ( empty( $site_name ) ) {
			$issues[] = __( 'Site name is not configured', 'wpshadow' );
		}

		// 4. Check for common email delivery issues.
		$from_email = get_option( 'admin_email' );
		if ( $from_email ) {
			$domain = wp_parse_url( get_site_url(), PHP_URL_HOST );
			$email_domain = substr( strrchr( $from_email, '@' ), 1 );

			// Check if from email matches site domain.
			if ( $email_domain !== $domain ) {
				$issues[] = sprintf(
					/* translators: 1: email domain, 2: site domain */
					__( 'From email domain (%1$s) does not match site domain (%2$s) - may be flagged as spam', 'wpshadow' ),
					esc_html( $email_domain ),
					esc_html( $domain )
				);
			}
		}

		// 5. Check if SMTP is configured (better deliverability).
		if ( ! defined( 'WPMS_ON' ) && ! defined( 'SMTP_HOST' ) ) {
			// Check for common SMTP plugins.
			$smtp_plugins = array(
				'wp-mail-smtp/wp_mail_smtp.php',
				'easy-wp-smtp/easy-wp-smtp.php',
				'post-smtp/postman-smtp.php',
				'wp-ses/wp-ses.php',
			);

			$has_smtp = false;
			foreach ( $smtp_plugins as $plugin ) {
				if ( is_plugin_active( $plugin ) ) {
					$has_smtp = true;
					break;
				}
			}

			if ( ! $has_smtp ) {
				$issues[] = __( 'No SMTP plugin detected - using default PHP mail() which has poor deliverability', 'wpshadow' );
			}
		}

		// 6. Check if export emails are being filtered.
		$export_email_filters = array(
			'wp_privacy_personal_data_email_content',
			'wp_privacy_personal_data_email_headers',
			'wp_privacy_personal_data_email_subject',
			'wp_privacy_personal_data_email_to',
		);

		foreach ( $export_email_filters as $filter ) {
			if ( has_filter( $filter ) ) {
				// Filters applied - could be good or bad.
				$filter_count = count( $GLOBALS['wp_filter'][ $filter ]->callbacks );
				if ( $filter_count > 2 ) { // More than default.
					$issues[] = sprintf(
						/* translators: %s: filter name */
						__( 'Export email filter "%s" has multiple callbacks - verify they work correctly', 'wpshadow' ),
						esc_html( $filter )
					);
				}
			}
		}

		// 7. Check upload directory for pending export files.
		$upload_dir = wp_upload_dir();
		$export_dir = trailingslashit( $upload_dir['basedir'] ) . 'wp-personal-data-exports';

		if ( file_exists( $export_dir ) ) {
			$files = glob( $export_dir . '/*.zip' );
			if ( is_array( $files ) && count( $files ) > 10 ) {
				$issues[] = sprintf(
					/* translators: %d: number of files */
					__( '%d old export files found - may indicate email delivery failures', 'wpshadow' ),
					count( $files )
				);
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'GDPR export email delivery may fail: %s', 'wpshadow' ),
				implode( '; ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 85,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/gdpr-export-email-delivery',
			'details'      => array(
				'issues'      => $issues,
				'admin_email' => $admin_email,
				'site_name'   => $site_name,
			),
		);
	}
}
