<?php
/**
 * Double Opt-In Implemented Diagnostic
 *
 * Tests whether the site enforces double opt-in to ensure high-quality email list.
 *
 * @since   1.26034.0310
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Double Opt-In Implemented Diagnostic Class
 *
 * Double opt-in ensures 99% real subscribers vs 70% with single opt-in.
 * Higher quality lists have better engagement and deliverability.
 *
 * @since 1.26034.0310
 */
class Diagnostic_Double_Opt_In_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'double-opt-in-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Double Opt-In Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site enforces double opt-in to ensure high-quality email list';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email-marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26034.0310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$optin_score = 0;
		$max_score = 4;

		// Check for email platform.
		$has_platform = self::check_email_platform();
		if ( $has_platform ) {
			$optin_score++;
		} else {
			$issues[] = __( 'No email marketing platform for opt-in management', 'wpshadow' );
		}

		// Check for double opt-in configuration.
		$double_optin = self::check_double_optin_enabled();
		if ( $double_optin ) {
			$optin_score++;
		} else {
			$issues[] = __( 'Double opt-in not enabled (accepting unverified subscribers)', 'wpshadow' );
		}

		// Check for confirmation email.
		$confirmation_email = self::check_confirmation_email();
		if ( $confirmation_email ) {
			$optin_score++;
		} else {
			$issues[] = __( 'No confirmation email template configured', 'wpshadow' );
		}

		// Check for GDPR compliance.
		$gdpr_compliant = self::check_gdpr_compliance();
		if ( $gdpr_compliant ) {
			$optin_score++;
		} else {
			$issues[] = __( 'Opt-in process may not be GDPR compliant', 'wpshadow' );
		}

		// Determine severity based on opt-in implementation.
		$optin_percentage = ( $optin_score / $max_score ) * 100;

		if ( $optin_percentage < 50 ) {
			$severity = 'medium';
			$threat_level = 35;
		} elseif ( $optin_percentage < 75 ) {
			$severity = 'low';
			$threat_level = 20;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Opt-in implementation percentage */
				__( 'Double opt-in implementation at %d%%. ', 'wpshadow' ),
				(int) $optin_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Double opt-in ensures 99% real subscribers vs 70% single opt-in', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/double-opt-in-implemented',
			);
		}

		return null;
	}

	/**
	 * Check email platform.
	 *
	 * @since  1.26034.0310
	 * @return bool True if platform exists, false otherwise.
	 */
	private static function check_email_platform() {
		$email_plugins = array(
			'mailpoet/mailpoet.php',
			'newsletter/newsletter.php',
			'mailchimp-for-wp/mailchimp-for-wp.php',
		);

		foreach ( $email_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check double opt-in enabled.
	 *
	 * @since  1.26034.0310
	 * @return bool True if enabled, false otherwise.
	 */
	private static function check_double_optin_enabled() {
		// MailPoet supports double opt-in.
		if ( is_plugin_active( 'mailpoet/mailpoet.php' ) ) {
			// Check if it's enabled in settings.
			$settings = get_option( 'mailpoet_settings', array() );
			if ( isset( $settings['signup_confirmation'] ) ) {
				return (bool) $settings['signup_confirmation']['enabled'];
			}
			return true; // Assume enabled by default.
		}

		// Newsletter plugin supports double opt-in.
		if ( is_plugin_active( 'newsletter/newsletter.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_double_optin_enabled', false );
	}

	/**
	 * Check confirmation email.
	 *
	 * @since  1.26034.0310
	 * @return bool True if template exists, false otherwise.
	 */
	private static function check_confirmation_email() {
		// Professional platforms have built-in templates.
		if ( is_plugin_active( 'mailpoet/mailpoet.php' ) ||
			 is_plugin_active( 'newsletter/newsletter.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_has_confirmation_email', false );
	}

	/**
	 * Check GDPR compliance.
	 *
	 * @since  1.26034.0310
	 * @return bool True if compliant, false otherwise.
	 */
	private static function check_gdpr_compliance() {
		// Check for GDPR plugins.
		$gdpr_plugins = array(
			'gdpr-cookie-consent/gdpr-cookie-consent.php',
			'cookie-law-info/cookie-law-info.php',
		);

		foreach ( $gdpr_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		// Modern email platforms are GDPR compliant.
		if ( is_plugin_active( 'mailpoet/mailpoet.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_optin_gdpr_compliant', false );
	}
}
