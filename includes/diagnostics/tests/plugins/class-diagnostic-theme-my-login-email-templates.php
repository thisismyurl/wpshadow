<?php
/**
 * Theme My Login Email Templates Diagnostic
 *
 * Theme My Login Email Templates issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1233.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme My Login Email Templates Diagnostic Class
 *
 * @since 1.1233.0000
 */
class Diagnostic_ThemeMyLoginEmailTemplates extends Diagnostic_Base {

	protected static $slug = 'theme-my-login-email-templates';
	protected static $title = 'Theme My Login Email Templates';
	protected static $description = 'Theme My Login Email Templates issue found';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Theme My Login plugin
		$has_tml = class_exists( 'Theme_My_Login' ) ||
		           defined( 'TML_VERSION' ) ||
		           function_exists( 'tml_get_option' );

		if ( ! $has_tml ) {
			return null;
		}

		$issues = array();

		// Check 1: Custom email templates
		$custom_templates = get_option( 'tml_use_custom_email_templates', 'no' );
		if ( 'no' === $custom_templates ) {
			$issues[] = __( 'Using default templates (generic branding)', 'wpshadow' );
		}

		// Check 2: HTML emails
		$html_emails = get_option( 'tml_html_emails', 'no' );
		if ( 'no' === $html_emails ) {
			$issues[] = __( 'Plain text emails (poor formatting)', 'wpshadow' );
		}

		// Check 3: From name
		$from_name = get_option( 'tml_from_name', '' );
		if ( empty( $from_name ) || $from_name === get_bloginfo( 'name' ) ) {
			$issues[] = __( 'Generic from name (spam filters)', 'wpshadow' );
		}

		// Check 4: Reply-to address
		$reply_to = get_option( 'tml_reply_to', '' );
		if ( empty( $reply_to ) ) {
			$issues[] = __( 'No reply-to (user replies bounce)', 'wpshadow' );
		}

		// Check 5: Email validation
		$validate_emails = get_option( 'tml_validate_email_addresses', 'no' );
		if ( 'no' === $validate_emails ) {
			$issues[] = __( 'No email validation (typos undetected)', 'wpshadow' );
		}

		// Check 6: Email rate limiting
		$rate_limit = get_option( 'tml_email_rate_limit', 0 );
		if ( $rate_limit === 0 ) {
			$issues[] = __( 'No rate limiting (spam abuse)', 'wpshadow' );
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
				__( 'Theme My Login email templates have %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/theme-my-login-email-templates',
		);
	}
}
