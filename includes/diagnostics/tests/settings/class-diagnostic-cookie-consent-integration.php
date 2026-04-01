<?php
/**
 * Cookie Consent Integration Diagnostic
 *
 * Validates that a cookie consent mechanism is present and integrated
 * for compliance with privacy regulations (GDPR/CCPA).
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cookie Consent Integration Diagnostic Class
 *
 * Checks for cookie consent integration and configuration.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Cookie_Consent_Integration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cookie-consent-integration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cookie Consent Integration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates cookie consent integration for privacy compliance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for common cookie consent plugins.
		$consent_plugins = array(
			'cookie-law-info/cookie-law-info.php'         => 'CookieYes',
			'cookie-notice/cookie-notice.php'             => 'Cookie Notice',
			'complianz-gdpr/complianz-gpdr.php'            => 'Complianz',
			'cookiebot/cookiebot.php'                     => 'Cookiebot',
			'gdpr-cookie-compliance/moove-gdpr.php'        => 'GDPR Cookie Compliance',
			'borlabs-cookie/borlabs-cookie.php'            => 'Borlabs Cookie',
			'cookie-consent/cookie-consent.php'            => 'Cookie Consent',
		);

		$active_plugins = array();
		foreach ( $consent_plugins as $plugin_path => $name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$active_plugins[] = $name;
			}
		}

		if ( empty( $active_plugins ) ) {
			$issues[] = __( 'No cookie consent plugin detected (privacy compliance risk)', 'wpshadow' );
		}

		// Check for custom cookie consent implementation.
		$template_dir = get_template_directory();
		$header_file  = $template_dir . '/header.php';
		$footer_file  = $template_dir . '/footer.php';

		if ( file_exists( $header_file ) ) {
			$header_content = file_get_contents( $header_file );
			if ( false !== stripos( $header_content, 'cookie' ) && false !== stripos( $header_content, 'consent' ) ) {
				// Custom implementation detected.
			}
		}

		if ( file_exists( $footer_file ) ) {
			$footer_content = file_get_contents( $footer_file );
			if ( false !== stripos( $footer_content, 'cookie' ) && false !== stripos( $footer_content, 'consent' ) ) {
				// Custom implementation detected.
			}
		}

		// Check for privacy policy page.
		$privacy_page_id = get_option( 'wp_page_for_privacy_policy' );
		if ( empty( $privacy_page_id ) ) {
			$issues[] = __( 'Privacy Policy page not configured', 'wpshadow' );
		}

		// Check if site uses cookies (WordPress sets authentication cookies by default).
		$site_uses_cookies = true;

		if ( $site_uses_cookies && empty( $active_plugins ) ) {
			$issues[] = __( 'Site uses cookies but no consent banner is active', 'wpshadow' );
		}

		// Check for consent log or recordkeeping (for GDPR).
		$consent_log_options = array( 'cookie_notice_accepted', 'moove_gdpr_consent' );
		$has_consent_logging = false;

		foreach ( $consent_log_options as $option ) {
			if ( get_option( $option ) ) {
				$has_consent_logging = true;
				break;
			}
		}

		if ( ! $has_consent_logging && ! empty( $active_plugins ) ) {
			$issues[] = __( 'Cookie consent logging not detected (may be required for GDPR compliance)', 'wpshadow' );
		}

		// Check for geolocation-based consent (optional but recommended).
		$has_geo_consent = false;

		if ( is_plugin_active( 'complianz-gdpr/complianz-gpdr.php' ) ) {
			$has_geo_consent = true;
		}

		if ( ! $has_geo_consent && ! empty( $active_plugins ) ) {
			$issues[] = __( 'Geolocation-based cookie consent not detected (consider for EU compliance)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of consent issues */
					__( 'Found %d cookie consent integration issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'details'      => array(
					'issues'         => $issues,
					'active_plugins' => $active_plugins,
					'recommendation' => __( 'Install and configure a cookie consent plugin (CookieYes, Complianz, or Cookiebot). Ensure consent is recorded and displayed before non-essential cookies load.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
