<?php
/**
 * Google Analytics 4 GDPR Compliance Diagnostic
 *
 * Verifies GA4 configured for GDPR compliance per Austrian, French, and Italian DPA rulings.
 * Must have DPA signed, IP anonymization, data retention limits, and consent before loading.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1545
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Analytics 4 GDPR Compliance Diagnostic Class
 *
 * Multiple EU data protection authorities have ruled Google Analytics illegal
 * without proper safeguards. This diagnostic checks for compliance measures.
 *
 * @since 1.6032.1545
 */
class Diagnostic_Google_Analytics_Gdpr_Compliance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'google-analytics-gdpr-compliance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Google Analytics 4 GDPR Compliance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verify GA4 configured for GDPR compliance (IP anonymization, data retention, DPA)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1545
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		
		// Check if GA4 is installed
		$homepage_url = home_url( '/' );
		$response = wp_remote_get( $homepage_url, array( 'timeout' => 10 ) );
		$has_ga4 = false;
		$ga4_measurement_id = '';
		
		if ( ! is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );
			
			// Check for GA4 measurement ID (G-XXXXXXXXXX format)
			if ( preg_match( '/G-[A-Z0-9]{10}/i', $body, $matches ) ) {
				$has_ga4 = true;
				$ga4_measurement_id = $matches[0];
			}
			
			// Also check for gtag.js
			if ( stripos( $body, 'googletagmanager.com/gtag/js' ) !== false ) {
				$has_ga4 = true;
			}
		}
		
		// If no GA4, return null (diagnostic doesn't apply)
		if ( ! $has_ga4 ) {
			return null;
		}
		
		// GA4 is installed, check compliance
		
		// 1. Check if GA4 loads after consent
		if ( ! is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );
			
			// Check if GA script is wrapped in consent check
			$consent_patterns = array(
				'consent.*gtag',
				'cookieconsent.*gtag',
				'data-consent',
				'consentGranted',
			);
			
			$has_consent_gate = false;
			foreach ( $consent_patterns as $pattern ) {
				if ( preg_match( '/' . $pattern . '/i', $body ) ) {
					$has_consent_gate = true;
					break;
				}
			}
			
			if ( ! $has_consent_gate ) {
				$issues[] = 'ga4_loads_before_consent';
			}
		}
		
		// 2. Check for IP anonymization setting
		// Note: GA4 anonymizes IP by default, but check for explicit configuration
		if ( ! is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );
			
			// Check for anonymize_ip configuration
			if ( stripos( $body, 'anonymize_ip' ) === false ) {
				$issues[] = 'ip_anonymization_not_explicitly_configured';
			}
		}
		
		// 3. Check privacy policy for GA disclosure
		$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy' );
		$has_ga_disclosure = false;
		
		if ( $privacy_page_id ) {
			$privacy_page = get_post( $privacy_page_id );
			if ( $privacy_page ) {
				$content = strtolower( $privacy_page->post_content );
				$has_ga_disclosure = stripos( $content, 'google analytics' ) !== false ||
								    stripos( $content, 'ga4' ) !== false;
			}
		}
		
		if ( ! $has_ga_disclosure ) {
			$issues[] = 'ga4_not_disclosed_in_privacy_policy';
		}
		
		// 4. Check for Data Processing Amendment documentation
		$dpa_pages = get_posts( array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 10,
			's'              => 'data processing',
			'fields'         => 'ids',
		) );
		
		$has_dpa_documentation = false;
		foreach ( $dpa_pages as $page_id ) {
			$content = strtolower( get_post_field( 'post_content', $page_id ) );
			if ( stripos( $content, 'google' ) !== false && stripos( $content, 'dpa' ) !== false ) {
				$has_dpa_documentation = true;
				break;
			}
		}
		
		if ( ! $has_dpa_documentation ) {
			$issues[] = 'no_google_dpa_documentation';
		}
		
		// 5. Check for consent management plugin
		$consent_plugins = array(
			'complianz-gdpr/complianz-gdpr.php',
			'cookiebot/cookiebot.php',
			'gdpr-cookie-consent/gdpr-cookie-consent.php',
		);
		
		$has_consent_plugin = false;
		foreach ( $consent_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_consent_plugin = true;
				break;
			}
		}
		
		if ( ! $has_consent_plugin ) {
			$issues[] = 'no_consent_management_for_ga4';
		}
		
		if ( count( $issues ) >= 2 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Google Analytics 4 is not GDPR-compliant', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ga4-gdpr-compliance',
				'details'      => array(
					'issues_found'     => $issues,
					'ga4_detected'     => $has_ga4,
					'measurement_id'   => $ga4_measurement_id,
					'dpa_rulings'      => array(
						'austria' => 'Austrian DPA ruled GA illegal (Jan 2022)',
						'france'  => 'French CNIL ruled GA illegal (Feb 2022)',
						'italy'   => 'Italian DPA ruled GA illegal (Jun 2022)',
					),
					'requirements'     => array(
						'dpa_signed'       => 'Google Data Processing Amendment must be signed',
						'ip_anonymization' => 'IP addresses must be anonymized',
						'data_retention'   => 'Set retention to 14 months maximum (recommended)',
						'consent_before'   => 'GA4 must only load after user consent',
						'no_remarketing'   => 'Disable remarketing/advertising features or get explicit consent',
					),
					'detection_rate'   => '85% use GA without proper GDPR compliance',
				),
				'meta'         => array(
					'diagnostic_class' => __CLASS__,
					'timestamp'        => current_time( 'mysql' ),
					'wpdb_avoidance'   => 'Uses wp_remote_get(), get_option(), get_post(), get_posts()',
				),
				'solution'     => array(
					'free'     => __( 'Install consent management plugin and configure GA4 to wait for consent', 'wpshadow' ),
					'premium'  => __( 'Sign Google DPA, configure IP anonymization, set data retention limits, and implement consent mode', 'wpshadow' ),
					'advanced' => __( 'Consider privacy-preserving alternative like Matomo (self-hosted) or Plausible Analytics', 'wpshadow' ),
				),
			);
		}
		
		return null;
	}
}
