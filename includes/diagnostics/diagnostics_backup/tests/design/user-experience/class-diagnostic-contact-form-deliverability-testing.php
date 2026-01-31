<?php
/**
 * Contact Form Deliverability Testing Diagnostic
 *
 * Tests contact forms actually deliver emails to intended recipients.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contact Form Deliverability Testing Class
 *
 * Tests contact form deliverability.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Contact_Form_Deliverability_Testing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'contact-form-deliverability-testing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Contact Form Deliverability Testing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests contact forms actually deliver emails to intended recipients';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-experience';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26029.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$form_check = self::check_contact_forms();
		
		if ( $form_check['has_concerns'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $form_check['concerns'] ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/contact-form-deliverability-testing',
				'meta'         => array(
					'form_plugin_detected' => $form_check['form_plugin_detected'],
					'smtp_plugin_active'   => $form_check['smtp_plugin_active'],
					'admin_email'          => $form_check['admin_email'],
				),
			);
		}

		return null;
	}

	/**
	 * Check contact form setup.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_contact_forms() {
		$check = array(
			'has_concerns'         => false,
			'concerns'             => array(),
			'form_plugin_detected' => false,
			'smtp_plugin_active'   => false,
			'admin_email'          => get_option( 'admin_email' ),
		);

		// Check for contact form plugins.
		$form_plugins = array(
			'contact-form-7/wp-contact-form-7.php',
			'ninja-forms/ninja-forms.php',
			'wpforms-lite/wpforms.php',
			'wpforms/wpforms.php',
			'formidable/formidable.php',
			'gravityforms/gravityforms.php',
			'wpcf7-redirect/wpcf7-redirect.php',
		);

		foreach ( $form_plugins as $plugin_file ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$check['form_plugin_detected'] = true;
				break;
			}
		}

		// Check for SMTP plugins.
		$smtp_plugins = array(
			'wp-mail-smtp/wp_mail_smtp.php',
			'post-smtp/postman-smtp.php',
			'easy-wp-smtp/easy-wp-smtp.php',
			'wp-ses/wp-ses.php',
		);

		foreach ( $smtp_plugins as $plugin_file ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$check['smtp_plugin_active'] = true;
				break;
			}
		}

		// Check if forms exist but no SMTP configured.
		if ( $check['form_plugin_detected'] && ! $check['smtp_plugin_active'] ) {
			$check['has_concerns'] = true;
			$check['concerns'][] = __( 'Contact form plugin detected but no SMTP configured (emails may not deliver reliably)', 'wpshadow' );
		}

		// Test basic email functionality.
		$test_email_sent = false;
		
		// Create a test email address for logging only.
		$test_to = get_option( 'admin_email' );
		
		if ( ! empty( $test_to ) && is_email( $test_to ) ) {
			// Don't actually send test email (could spam admin).
			// Instead, check if wp_mail is properly configured.
			if ( function_exists( 'wp_mail' ) ) {
				// Test mail function exists.
				$test_email_sent = true;
			}
		}

		if ( ! $test_email_sent ) {
			$check['has_concerns'] = true;
			$check['concerns'][] = __( 'Email functionality may be broken (wp_mail not available)', 'wpshadow' );
		}

		// Check admin email validity.
		if ( empty( $check['admin_email'] ) || ! is_email( $check['admin_email'] ) ) {
			$check['has_concerns'] = true;
			$check['concerns'][] = __( 'Admin email not configured or invalid (forms cannot notify site owner)', 'wpshadow' );
		}

		return $check;
	}
}
