<?php
/**
 * Cookie Consent Not Implemented Diagnostic
 *
 * Checks cookie consent.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Cookie_Consent_Not_Implemented Class
 *
 * Performs diagnostic check for Cookie Consent Not Implemented.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Cookie_Consent_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cookie-consent-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cookie Consent Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks cookie consent';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for cookie consent plugins.
		$consent_plugins = array(
			'cookie-notice/cookie-notice.php'                     => 'Cookie Notice',
			'gdpr-cookie-consent/gdpr-cookie-consent.php'         => 'GDPR Cookie Consent',
			'complianz-gdpr/complianz-gpdr.php'                   => 'Complianz',
			'cookie-law-info/cookie-law-info.php'                 => 'Cookie Law Info',
			'wp-gdpr-compliance/wp-gdpr-compliance.php'           => 'WP GDPR Compliance',
			'cookiebot/cookiebot.php'                            => 'Cookiebot',
		);

		$plugin_detected = false;
		$plugin_name     = '';

		foreach ( $consent_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$plugin_detected = true;
				$plugin_name     = $name;
				break;
			}
		}

		// Check for custom cookie consent implementation.
		$has_custom_consent = has_action( 'wp_footer', 'cookie_consent_banner' ) || 
		                       has_action( 'wp_head', 'cookie_consent' );

		// Check if site uses tracking/analytics (makes consent more important).
		global $wp_scripts;
		$uses_tracking = false;
		$tracking_services = array();

		if ( ! empty( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( ! empty( $script->src ) ) {
					if ( strpos( $script->src, 'google-analytics' ) !== false || 
					     strpos( $script->src, 'googletagmanager' ) !== false ) {
						$uses_tracking = true;
						$tracking_services[] = 'Google Analytics';
					}
					if ( strpos( $script->src, 'facebook.net' ) !== false ) {
						$uses_tracking = true;
						$tracking_services[] = 'Facebook Pixel';
					}
				}
			}
		}

		// Check site location/target audience (EU = GDPR required).
		$site_locale = get_locale();
		$eu_locale = in_array( 
			substr( $site_locale, 0, 2 ), 
			array( 'de', 'fr', 'es', 'it', 'nl', 'pl', 'pt', 'sv', 'da', 'fi', 'no', 'cs', 'el', 'hu', 'ro', 'sk', 'bg', 'hr', 'lt', 'lv', 'et', 'sl', 'mt', 'cy', 'ga' ),
			true
		);

		// Critical if using tracking without consent.
		if ( ! $plugin_detected && ! $has_custom_consent && $uses_tracking ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: comma-separated list of tracking services */
					__( 'Cookie consent not implemented but tracking detected (%s). GDPR/CCPA requires explicit user consent before setting non-essential cookies. Install Cookie Notice or GDPR Cookie Consent plugin to comply with privacy regulations.', 'wpshadow' ),
					implode( ', ', $tracking_services )
				),
				'severity'    => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/gdpr-cookie-consent',
				'details'     => array(
					'plugin_detected'     => false,
					'uses_tracking'       => true,
					'tracking_services'   => $tracking_services,
					'eu_locale'           => $eu_locale,
					'recommendation'      => __( 'Install Cookie Notice (free, 600K+ installs) or GDPR Cookie Consent (free, comprehensive) immediately. Non-compliance fines: up to €20M or 4% of revenue.', 'wpshadow' ),
					'legal_requirements'  => array(
						'gdpr'  => 'EU: Explicit consent required before cookies',
						'ccpa'  => 'California: Right to opt-out required',
						'pecr'  => 'UK: Cookie consent mandatory',
						'lgpd'  => 'Brazil: User consent required',
					),
					'penalties'           => array(
						'gdpr_max_fine' => '€20 million or 4% annual revenue',
						'ccpa_violation' => '$2,500 - $7,500 per violation',
					),
				),
			);
		}

		// Medium if no tracking but good practice.
		if ( ! $plugin_detected && ! $has_custom_consent && ! $uses_tracking ) {
			return array(
				'id'          => self::$slug,
				'title'       => __( 'Cookie Consent Recommended', 'wpshadow' ),
				'description' => __( 'Cookie consent banner not detected. Even without tracking cookies, transparency builds trust with visitors. Consider implementing cookie consent as a best practice.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/gdpr-cookie-consent',
				'details'     => array(
					'plugin_detected'   => false,
					'uses_tracking'     => false,
					'recommendation'    => __( 'Install Cookie Notice plugin for transparency. Shows professionalism and builds user trust.', 'wpshadow' ),
				),
			);
		}

		// No issues - cookie consent implemented.
		return null;
	}
}
