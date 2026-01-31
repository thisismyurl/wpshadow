<?php
/**
 * Third-Party Cookie Audit Diagnostic
 *
 * Identifies all third-party cookies and verifies proper disclosure. GDPR and ePrivacy
 * require identifying and disclosing all cookies, especially tracking/advertising cookies.
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
 * Third-Party Cookie Audit Diagnostic Class
 *
 * Cookie scanners often reveal undisclosed cookies. Each undisclosed cookie
 * is a potential violation of transparency requirements.
 *
 * @since 1.6032.1545
 */
class Diagnostic_Third_Party_Cookie_Audit extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'third-party-cookie-audit';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Third-Party Cookie Audit';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identify all third-party cookies and verify disclosure';

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
		$third_party_services = array();
		
		// Check homepage for third-party scripts/cookies
		$homepage_url = home_url( '/' );
		$response = wp_remote_get( $homepage_url, array( 'timeout' => 10 ) );
		
		if ( is_wp_error( $response ) ) {
			return null;
		}
		
		$body = wp_remote_retrieve_body( $response );
		
		// Known third-party cookie sources
		$cookie_sources = array(
			'google-analytics.com' => 'Google Analytics',
			'googletagmanager.com' => 'Google Tag Manager',
			'facebook.com'         => 'Facebook Pixel',
			'doubleclick.net'      => 'Google DoubleClick',
			'youtube.com'          => 'YouTube',
			'twitter.com'          => 'Twitter',
			'linkedin.com'         => 'LinkedIn',
			'hotjar.com'           => 'Hotjar',
			'cloudflare.com'       => 'Cloudflare',
			'googlesyndication.com' => 'Google AdSense',
		);
		
		// Detect which services are present
		foreach ( $cookie_sources as $domain => $service_name ) {
			if ( stripos( $body, $domain ) !== false ) {
				$third_party_services[] = $service_name;
			}
		}
		
		if ( count( $third_party_services ) === 0 ) {
			return null; // No third-party services detected
		}
		
		// Check if cookie policy exists
		$cookie_pages = get_posts( array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 10,
			's'              => 'cookie',
			'fields'         => 'ids',
		) );
		
		$has_cookie_policy = count( $cookie_pages ) > 0;
		
		if ( ! $has_cookie_policy ) {
			$issues[] = 'no_cookie_policy';
		}
		
		// Check privacy policy for cookie disclosure
		$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy' );
		$disclosed_services = array();
		
		if ( $privacy_page_id ) {
			$privacy_page = get_post( $privacy_page_id );
			if ( $privacy_page ) {
				$content = strtolower( $privacy_page->post_content );
				
				foreach ( $third_party_services as $service ) {
					if ( stripos( $content, strtolower( $service ) ) !== false ) {
						$disclosed_services[] = $service;
					}
				}
			}
		}
		
		// Check cookie policy pages
		foreach ( $cookie_pages as $page_id ) {
			$content = strtolower( get_post_field( 'post_content', $page_id ) );
			
			foreach ( $third_party_services as $service ) {
				if ( ! in_array( $service, $disclosed_services, true ) ) {
					if ( stripos( $content, strtolower( $service ) ) !== false ) {
						$disclosed_services[] = $service;
					}
				}
			}
		}
		
		$undisclosed_services = array_diff( $third_party_services, $disclosed_services );
		
		if ( count( $undisclosed_services ) > 0 ) {
			$issues[] = 'undisclosed_third_party_cookies';
		}
		
		// Check for cookie lifespan disclosure
		$has_lifespan_info = false;
		foreach ( $cookie_pages as $page_id ) {
			$content = strtolower( get_post_field( 'post_content', $page_id ) );
			if ( stripos( $content, 'days' ) !== false || 
				 stripos( $content, 'months' ) !== false ||
				 stripos( $content, 'expir' ) !== false ) {
				$has_lifespan_info = true;
				break;
			}
		}
		
		if ( ! $has_lifespan_info && count( $third_party_services ) > 0 ) {
			$issues[] = 'no_cookie_lifespan_disclosure';
		}
		
		// Check for advertising/tracking cookie consent
		$has_tracking_cookies = in_array( 'Google Analytics', $third_party_services, true ) ||
							   in_array( 'Facebook Pixel', $third_party_services, true ) ||
							   in_array( 'Google DoubleClick', $third_party_services, true );
		
		if ( $has_tracking_cookies ) {
			// Check if consent plugin is active
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
				$issues[] = 'tracking_cookies_without_consent_mechanism';
			}
		}
		
		if ( count( $issues ) >= 2 || count( $undisclosed_services ) >= 2 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Third-party cookies are not properly disclosed or managed', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/third-party-cookie-audit',
				'details'      => array(
					'issues_found'        => $issues,
					'detected_services'   => $third_party_services,
					'undisclosed_services' => $undisclosed_services,
					'disclosed_services'  => $disclosed_services,
					'requirements'        => array(
						'inventory'  => 'Must maintain complete cookie inventory',
						'disclosure' => 'All cookies must be disclosed in cookie/privacy policy',
						'purpose'    => 'Cookie purposes must be explained',
						'lifespan'   => 'Cookie lifespans must be disclosed',
						'third_party' => 'Third-party cookies must be identified as such',
						'consent'    => 'Tracking/advertising cookies require consent',
					),
					'detection_rate'      => '70% of websites have undisclosed cookies',
				),
				'meta'         => array(
					'diagnostic_class' => __CLASS__,
					'timestamp'        => current_time( 'mysql' ),
					'wpdb_avoidance'   => 'Uses wp_remote_get(), get_posts(), get_post_field(), get_option()',
				),
				'solution'     => array(
					'free'     => __( 'Use free cookie scanner (Cookiebot, OneTrust) to audit cookies and update policy', 'wpshadow' ),
					'premium'  => __( 'Install cookie consent plugin with automatic cookie detection and policy generation', 'wpshadow' ),
					'advanced' => __( 'Implement cookie consent management platform with regular automated audits and policy updates', 'wpshadow' ),
				),
			);
		}
		
		return null;
	}
}
