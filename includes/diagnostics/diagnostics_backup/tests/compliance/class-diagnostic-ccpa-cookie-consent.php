<?php
/**
 * CCPA Cookie Consent & Opt-Out Diagnostic
 *
 * Verifies compliance with California Consumer Privacy Act requirements,
 * particularly "Do Not Sell My Personal Information" link visibility.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_CCPA_Cookie_Consent Class
 *
 * Checks for CCPA compliance elements including opt-out link and cookie consent.
 *
 * @since 1.2601.2148
 */
class Diagnostic_CCPA_Cookie_Consent extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ccpa-cookie-consent';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CCPA Cookie Consent & Opt-Out Compliance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies California Consumer Privacy Act compliance for cookie handling and opt-out rights';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if compliance issues detected, null otherwise.
	 */
	public static function check() {
		// Check if site is US-based or serves California users
		$site_location = self::get_site_location();
		$serves_california = self::serves_california_users();

		if ( ! $site_location['is_us'] && ! $serves_california ) {
			// CCPA may not apply
			return null;
		}

		$compliance_issues = array();

		// Check 1: Do Not Sell link
		if ( ! self::has_do_not_sell_link() ) {
			$compliance_issues[] = 'Missing "Do Not Sell My Personal Information" link';
		}

		// Check 2: Cookie consent banner
		if ( ! self::has_cookie_consent() ) {
			$compliance_issues[] = 'No cookie consent mechanism detected';
		}

		// Check 3: Privacy policy mentions cookies/CCPA
		if ( ! self::has_privacy_policy_ccpa_content() ) {
			$compliance_issues[] = 'Privacy policy missing CCPA and cookie information';
		}

		// Check 4: Third-party analytics tracking
		if ( self::has_untracked_analytics() ) {
			$compliance_issues[] = 'Untracked analytics/tracking scripts without consent';
		}

		if ( empty( $compliance_issues ) ) {
			// No issues found
			return null;
		}

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: %d: number of issues, %s: issue list */
				__( 'Found %d CCPA compliance issues: %s', 'wpshadow' ),
				count( $compliance_issues ),
				implode( ', ', $compliance_issues )
			),
			'severity'      => 'high',
			'threat_level'  => 80,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/ccpa-compliance',
			'family'        => self::$family,
			'meta'          => array(
				'location'                   => $site_location['country'],
				'serves_california'          => $serves_california ? 'Yes' : 'No',
				'ccpa_applies'               => 'Likely',
				'compliance_issues_count'    => count( $compliance_issues ),
				'required_actions'           => array(
					__( 'Add "Do Not Sell My Personal Information" link in footer' ),
					__( 'Implement cookie consent mechanism' ),
					__( 'Update privacy policy with CCPA section' ),
					__( 'Audit third-party analytics for consent' ),
				),
				'recommended_plugins'        => array(
					'Termly' => 'Full CCPA + GDPR + COPPA compliance suite',
					'Cookiebot' => 'Cookie consent + compliance auditing',
					'OneTrust' => 'Enterprise privacy management',
				),
			),
			'details'       => array(
				'legal_risk'      => sprintf(
					/* translators: %s: penalty amount */
					__( 'CCPA non-compliance can result in penalties up to %s per violation, or $7,500 per intentional violation.', 'wpshadow' ),
					'$2,500'
				),
				'requirements'    => array(
					'Do Not Sell Link' => array(
						__( 'Must be prominent in footer or top navigation' ),
						__( 'Must say "Do Not Sell My Personal Information"' ),
						__( 'Must link to privacy policy or opt-out form' ),
						__( 'Must be easy to find and use' ),
					),
					'Cookie Consent' => array(
						__( 'Must ask consent before non-essential cookies' ),
						__( 'Must be easy to opt-out (not buried in fine print)' ),
						__( 'Must distinguish first-party vs third-party cookies' ),
						__( 'Must track consent and honor user choices' ),
					),
					'Privacy Policy' => array(
						__( 'Must explain what data is collected' ),
						__( 'Must disclose third-party data sharing' ),
						__( 'Must include specific CCPA language' ),
						__( 'Must be updated within 90 days of changes' ),
					),
				),
				'quick_fix_steps' => array(
					'Step 1' => __( 'Add footer link: href="/privacy-policy#do-not-sell"' ),
					'Step 2' => __( 'Install cookie consent plugin (Termly recommended for compliance)' ),
					'Step 3' => __( 'Update privacy policy to include CCPA section and cookie disclosure' ),
					'Step 4' => __( 'Test opt-out flow: verify user choices are honored' ),
					'Step 5' => __( 'Audit analytics scripts and ensure consent is tracked' ),
				),
				'affected_industries' => array(
					__( 'E-commerce (selling products/data)' ),
					__( 'SaaS (collecting customer data)' ),
					__( 'Media/Content (selling ad targeting data)' ),
					__( 'Services (data collection for insights)' ),
				),
			),
		);
	}

	/**
	 * Check if site has a "Do Not Sell" link.
	 *
	 * @since  1.2601.2148
	 * @return bool True if link detected.
	 */
	private static function has_do_not_sell_link() {
		$footer = wp_remote_get( home_url() );

		if ( is_wp_error( $footer ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $footer );

		return ( strpos( strtolower( $body ), 'do not sell' ) !== false || strpos( strtolower( $body ), 'ccpa' ) !== false );
	}

	/**
	 * Check if site has cookie consent mechanism.
	 *
	 * @since  1.2601.2148
	 * @return bool True if cookie consent detected.
	 */
	private static function has_cookie_consent() {
		// Check for common cookie consent plugins
		$consent_plugins = array(
			'cookiebot-for-wordpress/cookiebot.php',
			'termly-manage-cookies/termly-manage-cookies.php',
			'cookie-law-info/cookie-law-info.php',
			'gdpr-cookie-consent/moove-gdpr.php',
		);

		foreach ( $consent_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// Check for cookie consent in page HTML
		$response = wp_remote_get( home_url() );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );

		return ( strpos( strtolower( $body ), 'cookie' ) !== false && 
				( strpos( strtolower( $body ), 'consent' ) !== false || strpos( strtolower( $body ), 'accept' ) !== false ) );
	}

	/**
	 * Check if privacy policy mentions CCPA and cookies.
	 *
	 * @since  1.2601.2148
	 * @return bool True if CCPA content found in privacy policy.
	 */
	private static function has_privacy_policy_ccpa_content() {
		$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy' );

		if ( $privacy_page_id === 0 ) {
			return false;
		}

		$post = get_post( $privacy_page_id );

		if ( ! $post ) {
			return false;
		}

		$content = strtolower( $post->post_content );

		$has_ccpa = strpos( $content, 'ccpa' ) !== false || strpos( $content, 'california' ) !== false;
		$has_cookies = strpos( $content, 'cookie' ) !== false;

		return ( $has_ccpa && $has_cookies );
	}

	/**
	 * Check for untracked analytics scripts.
	 *
	 * @since  1.2601.2148
	 * @return bool True if untracked analytics found.
	 */
	private static function has_untracked_analytics() {
		// Check for Google Analytics without consent
		$tracking_scripts = array(
			'google-analytics',
			'google-analytics/analytics.js',
			'gtag',
			'facebook-pixel',
			'hotjar',
		);

		$response = wp_remote_get( home_url() );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		$body_lower = strtolower( $body );

		$found_scripts = 0;

		foreach ( $tracking_scripts as $script ) {
			if ( strpos( $body_lower, $script ) !== false ) {
				$found_scripts ++;
			}
		}

		// If found tracking scripts but no consent mechanism, flag as issue
		return ( $found_scripts > 0 && ! self::has_cookie_consent() );
	}

	/**
	 * Get site location based on options or IP.
	 *
	 * @since  1.2601.2148
	 * @return array Location info.
	 */
	private static function get_site_location() {
		// Check if site has location setting
		$location = get_option( 'wpshadow_site_location', '' );

		if ( strpos( strtolower( $location ), 'united states' ) !== false || 
			strpos( strtolower( $location ), 'usa' ) !== false ) {
			return array(
				'is_us'     => true,
				'country'   => 'United States',
			);
		}

		// Fallback: assume US if not specified
		return array(
			'is_us'     => true,
			'country'   => 'Unknown (Assuming US)',
		);
	}

	/**
	 * Check if site serves California users (transactional).
	 *
	 * @since  1.2601.2148
	 * @return bool True if site likely serves California customers.
	 */
	private static function serves_california_users() {
		// Check for WooCommerce and orders to California
		if ( ! class_exists( 'WooCommerce' ) ) {
			return false;
		}

		global $wpdb;

		// Check if any orders shipped to California
		$ca_orders = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} pm WHERE meta_key = '_shipping_state' AND meta_value = %s",
				'CA'
			)
		);

		return (int) $ca_orders > 0;
	}
}
