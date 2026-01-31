<?php
/**
 * Multisite Network Registration and Anti-Spam Diagnostic
 *
 * Checks if multisite networks implement proper registration controls,
 * CAPTCHA, email verification, and anti-spam measures for new sites.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Multisite
 * @since      1.6031.1457
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Multisite;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Registration Anti-Spam Diagnostic Class
 *
 * Verifies multisite networks implement registration controls and anti-spam.
 *
 * @since 1.6031.1457
 */
class Diagnostic_Multisite_Registration_Antispam extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'multisite-registration-antispam';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Multisite Network Registration and Anti-Spam';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies multisite networks implement registration controls and anti-spam measures';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'multisite';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6031.1457
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! is_multisite() ) {
			return null; // Not multisite.
		}

		$issues = array();

		// Check registration settings.
		$registration = get_site_option( 'registration' );
		if ( $registration === 'all' ) {
			$issues[] = __( 'Open site registration enabled (vulnerable to spam sites)', 'wpshadow' );
		}

		$active_plugins = get_option( 'active_plugins', array() );

		// Check for CAPTCHA on registration.
		$has_captcha = false;
		$captcha_plugins = array(
			'recaptcha',
			'captcha',
			'hcaptcha',
			'really-simple-captcha',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $captcha_plugins as $cap_plugin ) {
				if ( stripos( $plugin, $cap_plugin ) !== false ) {
					$has_captcha = true;
					break 2;
				}
			}
		}

		if ( ! $has_captcha && $registration !== 'none' ) {
			$issues[] = __( 'No CAPTCHA plugin detected for registration forms', 'wpshadow' );
		}

		// Check for anti-spam plugins.
		$has_antispam = false;
		$antispam_plugins = array(
			'akismet',
			'antispam-bee',
			'stop-spammer',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $antispam_plugins as $spam_plugin ) {
				if ( stripos( $plugin, $spam_plugin ) !== false ) {
					$has_antispam = true;
					break 2;
				}
			}
		}

		if ( ! $has_antispam ) {
			$issues[] = __( 'No anti-spam plugin detected', 'wpshadow' );
		}

		// Check email verification requirement.
		$email_verification = get_site_option( 'registration_notification' );
		if ( $email_verification !== 'both' && $registration !== 'none' ) {
			$issues[] = __( 'Email verification not required for new registrations', 'wpshadow' );
		}

		// Check for banned email domains.
		$banned_domains = get_site_option( 'banned_email_domains' );
		if ( empty( $banned_domains ) && $registration !== 'none' ) {
			$issues[] = __( 'No banned email domains configured (allows disposable email addresses)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Multisite registration/anti-spam concerns: %s. Networks with open registration should implement CAPTCHA, email verification, and anti-spam measures.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/multisite-registration-antispam',
		);
	}
}
