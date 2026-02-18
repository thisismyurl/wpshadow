<?php
/**
 * Admin Email Deliverability
 *
 * Checks if admin email is configured and deliverable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Configuration
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Admin_Email_Deliverability Class
 *
 * Validates admin email configuration and deliverability.
 *
 * @since 1.6030.2148
 */
class Diagnostic_Admin_Email_Deliverability extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-email-deliverability';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Admin Email Deliverability';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates admin email configuration and deliverability';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'configuration';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests admin email configuration.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$admin_email = get_option( 'admin_email', '' );

		// Check 1: Admin email is configured
		if ( empty( $admin_email ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Admin email address is not configured', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/admin-email-configuration',
				'recommendations' => array(
					__( 'Set valid admin email address', 'wpshadow' ),
					__( 'Use email address you actively monitor', 'wpshadow' ),
					__( 'Verify email delivery capability', 'wpshadow' ),
				),
			);
		}

		// Check 2: Email format validation
		if ( ! is_email( $admin_email ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Admin email format is invalid', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 80,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/email-format-validation',
				'recommendations' => array(
					__( 'Use valid email format (example@domain.com)', 'wpshadow' ),
					__( 'Check for typos in email address', 'wpshadow' ),
					__( 'Avoid special characters', 'wpshadow' ),
				),
			);
		}

		// Check 3: Domain has MX records
		if ( ! self::has_mx_records( $admin_email ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Email domain may not have MX records', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mx-records-email',
				'recommendations' => array(
					__( 'Verify domain has MX records configured', 'wpshadow' ),
					__( 'Contact hosting provider to check mail setup', 'wpshadow' ),
					__( 'Use email service provider if needed', 'wpshadow' ),
				),
			);
		}

		// Check 4: Email not localhost
		if ( strpos( $admin_email, '@localhost' ) !== false || strpos( $admin_email, '@127.0.0.1' ) !== false ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Admin email uses localhost - emails will not be delivered', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/email-localhost-issue',
				'recommendations' => array(
					__( 'Use real domain email address', 'wpshadow' ),
					__( 'Do not use localhost or 127.0.0.1', 'wpshadow' ),
					__( 'Use @yourdomain.com email address', 'wpshadow' ),
				),
			);
		}

		// Check 5: Mail plugin installed
		if ( ! self::has_mail_plugin() ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No email service plugin detected (may rely on server mail)', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/email-plugin-recommendation',
				'recommendations' => array(
					__( 'Consider installing email plugin (WP Mail SMTP, SendGrid, Mailgun)', 'wpshadow' ),
					__( 'Email plugins improve deliverability significantly', 'wpshadow' ),
					__( 'Especially important for sending newsletters and notifications', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check for MX records.
	 *
	 * @since  1.6030.2148
	 * @param  string $email Email address.
	 * @return bool True if MX records exist.
	 */
	private static function has_mx_records( $email ) {
		// Extract domain from email
		$domain = substr( strrchr( $email, '@' ), 1 );

		if ( empty( $domain ) ) {
			return false;
		}

		// Check for MX records
		if ( function_exists( 'checkdnsrr' ) ) {
			return checkdnsrr( $domain, 'MX' );
		}

		return true; // Assume OK if function not available
	}

	/**
	 * Check for mail plugin.
	 *
	 * @since  1.6030.2148
	 * @return bool True if mail plugin detected.
	 */
	private static function has_mail_plugin() {
		// Common email plugins
		$email_plugins = array(
			'wp-mail-smtp/wp_mail_smtp.php',
			'sendgrid-email-delivery-simplified/sendgrid.php',
			'mailgun/mailgun.php',
			'wp-postman/postman.php',
			'post-smtp/postman-smtp.php',
		);

		foreach ( $email_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}
}
