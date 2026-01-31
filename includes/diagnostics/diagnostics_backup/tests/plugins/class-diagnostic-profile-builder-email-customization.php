<?php
/**
 * Profile Builder Email Customization Diagnostic
 *
 * Profile Builder Email Customization issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1226.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Profile Builder Email Customization Diagnostic Class
 *
 * @since 1.1226.0000
 */
class Diagnostic_ProfileBuilderEmailCustomization extends Diagnostic_Base {

	protected static $slug = 'profile-builder-email-customization';
	protected static $title = 'Profile Builder Email Customization';
	protected static $description = 'Profile Builder Email Customization issue found';
	protected static $family = 'functionality';

	public static function check() {
		$has_pb = class_exists( 'Profile_Builder' ) ||
		          defined( 'PROFILE_BUILDER_VERSION' ) ||
		          function_exists( 'wppb_create_upload_form' );

		if ( ! $has_pb ) {
			return null;
		}

		$issues = array();

		// Check 1: Custom email templates
		$custom_emails = get_option( 'wppb_custom_email_templates', 'no' );
		if ( 'no' === $custom_emails ) {
			$issues[] = __( 'Using default templates (generic branding)', 'wpshadow' );
		}

		// Check 2: From name
		$from_name = get_option( 'wppb_email_from_name', '' );
		if ( empty( $from_name ) ) {
			$issues[] = __( 'No from name (spam filters)', 'wpshadow' );
		}

		// Check 3: From email
		$from_email = get_option( 'wppb_email_from_email', '' );
		if ( empty( $from_email ) || ! is_email( $from_email ) ) {
			$issues[] = __( 'Invalid from email (delivery issues)', 'wpshadow' );
		}

		// Check 4: Email content type
		$content_type = get_option( 'wppb_email_content_type', 'text/plain' );
		if ( 'text/plain' === $content_type ) {
			$issues[] = __( 'Plain text emails (poor formatting)', 'wpshadow' );
		}

		// Check 5: Variable sanitization
		$sanitize_vars = get_option( 'wppb_sanitize_email_vars', 'no' );
		if ( 'no' === $sanitize_vars ) {
			$issues[] = __( 'Variables not sanitized (XSS risk)', 'wpshadow' );
		}

		// Check 6: Email logging
		$log_emails = get_option( 'wppb_log_emails', 'no' );
		if ( 'no' === $log_emails ) {
			$issues[] = __( 'Emails not logged (no audit trail)', 'wpshadow' );
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
				__( 'Profile Builder email has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/profile-builder-email-customization',
		);
	}
}
