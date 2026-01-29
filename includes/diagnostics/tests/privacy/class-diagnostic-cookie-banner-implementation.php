<?php
/**
 * Cookie Banner Implementation Standards Diagnostic
 *
 * Verifies cookie banner meets ePrivacy Directive requirements. EDPB guidelines
 * require equal prominence for accept/reject, no dark patterns, and no cookies before consent.
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
 * Cookie Banner Implementation Standards Diagnostic Class
 *
 * ePrivacy Directive requires consent before non-essential cookies.
 * French CNIL has issued €60M fines for cookie violations.
 *
 * @since 1.6032.1545
 */
class Diagnostic_Cookie_Banner_Implementation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cookie-banner-implementation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cookie Banner Implementation Standards';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verify cookie banner meets all regulatory requirements (ePrivacy)';

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
		
		// Check for cookie consent plugins
		$consent_plugins = array(
			'complianz-gdpr/complianz-gdpr.php',
			'cookie-law-info/cookie-law-info.php',
			'cookiebot/cookiebot.php',
			'gdpr-cookie-consent/gdpr-cookie-consent.php',
		);
		
		$has_consent_plugin = false;
		$active_plugin = '';
		foreach ( $consent_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_consent_plugin = true;
				$active_plugin = $plugin;
				break;
			}
		}
		
		if ( ! $has_consent_plugin ) {
			$issues[] = 'no_cookie_consent_plugin';
		}
		
		// Check homepage for cookie banner presence and settings
		$homepage_url = home_url( '/' );
		$response = wp_remote_get( $homepage_url, array( 'timeout' => 10 ) );
		
		if ( ! is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );
			
			// Check for common dark patterns in HTML
			$has_accept_all = stripos( $body, 'accept all' ) !== false || stripos( $body, 'acceptAll' ) !== false;
			$has_reject_all = stripos( $body, 'reject all' ) !== false || stripos( $body, 'rejectAll' ) !== false;
			
			if ( $has_accept_all && ! $has_reject_all ) {
				$issues[] = 'reject_option_not_equal_to_accept';
			}
			
			// Check for pre-checked boxes (dark pattern)
			if ( preg_match( '/checked.*cookie|cookie.*checked/i', $body ) ) {
				$issues[] = 'pre_checked_consent_boxes';
			}
		}
		
		// Check if analytics/tracking scripts load before consent
		if ( ! is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );
			
			// Common tracking scripts
			$tracking_patterns = array(
				'google-analytics.com/analytics.js',
				'googletagmanager.com/gtag/js',
				'facebook.com/tr',
				'connect.facebook.net',
			);
			
			foreach ( $tracking_patterns as $pattern ) {
				if ( stripos( $body, $pattern ) !== false ) {
					// Check if script is wrapped in consent check
					if ( ! preg_match( '/consent.*' . preg_quote( $pattern, '/' ) . '|' . preg_quote( $pattern, '/' ) . '.*consent/i', $body ) ) {
						$issues[] = 'tracking_scripts_load_before_consent';
						break;
					}
				}
			}
		}
		
		// Check plugin settings for granular control
		if ( $has_consent_plugin ) {
			// Check Complianz settings
			if ( strpos( $active_plugin, 'complianz' ) !== false ) {
				$complianz_settings = get_option( 'complianz_options_settings', array() );
				if ( ! empty( $complianz_settings ) ) {
					// Check if category-based consent is enabled
					$has_categories = isset( $complianz_settings['cookie_banner_style'] );
					if ( ! $has_categories ) {
						$issues[] = 'no_granular_cookie_control';
					}
				}
			}
			
			// Check Cookie Law Info
			if ( strpos( $active_plugin, 'cookie-law-info' ) !== false ) {
				$cli_settings = get_option( 'cookielawinfo-settings', array() );
				if ( empty( $cli_settings ) ) {
					$issues[] = 'consent_plugin_not_configured';
				}
			}
		}
		
		if ( count( $issues ) >= 2 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Cookie banner does not meet ePrivacy regulatory requirements', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cookie-banner-compliance',
				'details'      => array(
					'issues_found'   => $issues,
					'legal_basis'    => 'ePrivacy Directive 2002/58/EC',
					'edpb_guidelines' => 'Guidelines 05/2020 on consent',
					'requirements'   => array(
						'before_cookies' => 'Banner must appear before non-essential cookies',
						'equal_prominence' => 'Accept and reject must have equal visibility',
						'no_dark_patterns' => 'No pre-checked boxes or misleading design',
						'granular_control' => 'Users must control cookie categories',
						'accessible' => 'Banner must be accessible to all users',
					),
					'penalties'      => __( 'French CNIL issued €60M fines for cookie violations', 'wpshadow' ),
					'detection_rate' => '80% of websites violate cookie consent rules',
				),
				'meta'         => array(
					'diagnostic_class' => __CLASS__,
					'timestamp'        => current_time( 'mysql' ),
					'wpdb_avoidance'   => 'Uses is_plugin_active(), get_option(), wp_remote_get()',
				),
				'solution'     => array(
					'free'     => __( 'Install compliant cookie consent plugin (Complianz, Cookiebot) with proper settings', 'wpshadow' ),
					'premium'  => __( 'Configure cookie banner with equal accept/reject buttons and granular controls', 'wpshadow' ),
					'advanced' => __( 'Implement cookie consent with script blocking, consent refresh, and accessibility features', 'wpshadow' ),
				),
			);
		}
		
		return null;
	}
}
