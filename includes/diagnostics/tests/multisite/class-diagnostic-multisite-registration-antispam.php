<?php
/**
 * Multisite Registration Anti-Spam Protection Diagnostic
 *
 * Verifies network registration has anti-spam measures
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6031.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Multisite;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
 * Diagnostic_MultisiteRegistrationAntispam Class
 *
 * Checks for CAPTCHA, email verification, banned domains
 *
 * @since 1.6031.1445
 */
class Diagnostic_MultisiteRegistrationAntispam extends Diagnostic_Base {

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
protected static $title = 'Multisite Registration Anti-Spam Protection';

/**
 * The diagnostic description
 *
 * @var string
 */
protected static $description = 'Verifies network registration has anti-spam measures';

/**
 * The family this diagnostic belongs to
 *
 * @var string
 */
protected static $family = 'multisite';

/**
 * Run the diagnostic check.
 *
 * @since  1.6031.1445
 * @return array|null Finding array if issue found, null otherwise.
 */
public static function check() {
		// Only run on multisite.
		if ( ! is_multisite() ) {
			return null;
		}

		$issues = array();

		// Check if user registration is open.
		$registration = get_site_option( 'registration', 'none' );
		if ( 'none' !== $registration ) {
			// Registration is enabled, check for spam protection.

			// Check for CAPTCHA on signup.
			$active_plugins = get_site_option( 'active_sitewide_plugins', array() );
			$captcha_plugins = array( 'recaptcha', 'captcha', 'hcaptcha', 'turnstile' );
			$has_captcha = false;

			foreach ( array_keys( $active_plugins ) as $plugin ) {
				foreach ( $captcha_plugins as $cap_plugin ) {
					if ( stripos( $plugin, $cap_plugin ) !== false ) {
						$has_captcha = true;
						break 2;
					}
				}
			}

			if ( ! $has_captcha ) {
				$issues[] = __( 'No CAPTCHA plugin for user registration', 'wpshadow' );
			}

			// Check for antispam plugins.
			$antispam_plugins = array( 'akismet', 'antispam', 'stop-spammer', 'cleantalk' );
			$has_antispam = false;

			foreach ( array_keys( $active_plugins ) as $plugin ) {
				foreach ( $antispam_plugins as $spam_plugin ) {
					if ( stripos( $plugin, $spam_plugin ) !== false ) {
						$has_antispam = true;
							break 2;
					}
				}
			}

			if ( ! $has_antispam ) {
				$issues[] = __( 'No antispam plugin for network registration', 'wpshadow' );
			}

			// Check for email verification.
			$limited_email = get_site_option( 'limited_email_domains', '' );
			if ( empty( $limited_email ) ) {
				$issues[] = __( 'Email domain restrictions not configured', 'wpshadow' );
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
				__( 'Multisite registration concerns: %s. Open registration needs spam protection.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 65,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/multisite-registration-antispam',
		);
