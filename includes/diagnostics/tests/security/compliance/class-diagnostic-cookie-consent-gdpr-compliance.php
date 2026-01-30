<?php
/**
 * Diagnostic: Cookie Consent GDPR Compliance
 *
 * Validates cookie consent banner meets GDPR/CCPA requirements.
 * GDPR fines up to €20M or 4% revenue for non-compliance.
 * Must block cookies until explicit consent given.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Compliance
 * @since      1.26028.1913
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Cookie_Consent_Gdpr_Compliance
 *
 * Tests GDPR cookie consent compliance.
 *
 * @since 1.26028.1913
 */
class Diagnostic_Cookie_Consent_Gdpr_Compliance extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'cookie-consent-gdpr-compliance';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Cookie Consent GDPR Compliance';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates cookie consent banner meets GDPR/CCPA requirements';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Check GDPR cookie consent compliance.
	 *
	 * @since  1.26028.1913
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();

		$has_consent_plugin = self::detect_consent_plugin();

		if ( ! $has_consent_plugin ) {
			if ( self::uses_analytics_or_tracking() ) {
				$issues[] = __( 'Using analytics/tracking without cookie consent banner', 'wpshadow' );
			}
		}

		if ( self::sets_cookies_without_consent() ) {
			$issues[] = __( 'Setting cookies before user consent', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$severity = self::uses_analytics_or_tracking() ? 'critical' : 'high';
			$threat_level = self::uses_analytics_or_tracking() ? 80 : 70;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: Number of issues, 2: List of issues */
					__( 'Detected %1$d GDPR compliance issue(s): %2$s. GDPR fines up to €20M or 4%% revenue. Must obtain explicit consent before setting cookies.', 'wpshadow' ),
					count( $issues ),
					implode( '; ', $issues )
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cookie-consent-gdpr-compliance',
				'meta'         => array(
					'issues'               => $issues,
					'has_consent_plugin'   => $has_consent_plugin,
					'uses_tracking'        => self::uses_analytics_or_tracking(),
					'recommendation'       => 'Install GDPR-compliant cookie consent plugin',
				),
			);
		}

		return null;
	}

	/**
	 * Detect cookie consent plugin.
	 *
	 * @since  1.26028.1913
	 * @return bool True if consent plugin detected, false otherwise.
	 */
	private static function detect_consent_plugin() {
		$consent_plugins = array(
			'cookie-law-info/cookie-law-info.php',
			'complianz-gdpr/complianz-gpdr.php',
			'gdpr-cookie-consent/gdpr-cookie-consent.php',
			'cookie-notice/cookie-notice.php',
			'cookiebot/cookiebot.php',
			'uk-cookie-consent/uk-cookie-consent.php',
			'wp-gdpr-compliance/wp-gdpr-compliance.php',
		);

		foreach ( $consent_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if site uses analytics or tracking.
	 *
	 * @since  1.26028.1913
	 * @return bool True if tracking detected, false otherwise.
	 */
	private static function uses_analytics_or_tracking() {
		$analytics_plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php',
			'google-analytics-dashboard-for-wp/gadwp.php',
			'ga-google-analytics/ga-google-analytics.php',
			'monster-insights/monsterinsights.php',
			'exactmetrics/exactmetrics.php',
		);

		foreach ( $analytics_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		if ( class_exists( 'WooCommerce' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if cookies are set without consent.
	 *
	 * @since  1.26028.1913
	 * @return bool True if cookies set prematurely, false otherwise.
	 */
	private static function sets_cookies_without_consent() {
		$cookies = $_COOKIE;

		$tracking_cookies = array();
		foreach ( $cookies as $name => $value ) {
			if ( preg_match( '/^(_ga|_gid|_gat|__utm|_fbp|fr|_gcl)/', $name ) ) {
				$tracking_cookies[] = $name;
			}
		}

		return ! empty( $tracking_cookies );
	}
}
