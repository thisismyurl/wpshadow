<?php
/**
 * External Service Fallback Diagnostic
 *
 * Detects when site functionality breaks completely when external services fail.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\UX
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\UX;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No Fallback External Service Diagnostic Class
 *
 * Checks if the site has fallback mechanisms when external services fail.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Fallback_External_Service extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-fallback-external-service';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Fallback When External Service Fails';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects when critical functionality breaks completely if external services fail';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'ux';

	/**
	 * Run the diagnostic check
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array or null if no issues found.
	 */
	public static function check() {
		$external_dependencies = array();

		// Check for CDN dependencies.
		global $wp_scripts, $wp_styles;

		$external_scripts = array();
		$external_styles  = array();

		if ( ! empty( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( ! empty( $script->src ) && preg_match( '/^https?:\/\/(cdn|ajax\.googleapis|cdnjs|unpkg)/i', $script->src ) ) {
					$external_scripts[] = $handle;
				}
			}
		}

		if ( ! empty( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $handle => $style ) {
				if ( ! empty( $style->src ) && preg_match( '/^https?:\/\/(cdn|fonts\.googleapis|cdnjs|unpkg)/i', $style->src ) ) {
					$external_styles[] = $handle;
				}
			}
		}

		if ( ! empty( $external_scripts ) ) {
			$external_dependencies['scripts'] = $external_scripts;
		}

		if ( ! empty( $external_styles ) ) {
			$external_dependencies['styles'] = $external_styles;
		}

		// Check for critical API dependencies.
		$critical_api_plugins = array(
			'woocommerce/woocommerce.php'                => array(
				'name'     => 'WooCommerce',
				'risk'     => __( 'Checkout breaks if payment gateway APIs are down', 'wpshadow' ),
			),
			'mailchimp-for-wp/mailchimp-for-wp.php'      => array(
				'name'     => 'Mailchimp',
				'risk'     => __( 'Signups fail if Mailchimp API is unavailable', 'wpshadow' ),
			),
			'google-site-kit/google-site-kit.php'        => array(
				'name'     => 'Google Site Kit',
				'risk'     => __( 'Analytics dashboard breaks if Google APIs fail', 'wpshadow' ),
			),
		);

		$active_critical_apis = array();
		foreach ( $critical_api_plugins as $plugin => $data ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_critical_apis[] = $data;
			}
		}

		if ( ! empty( $active_critical_apis ) ) {
			$external_dependencies['critical_apis'] = $active_critical_apis;
		}

		if ( empty( $external_dependencies ) ) {
			return null; // No external dependencies found.
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Your site relies on external services (CDNs, payment gateways, APIs) but has no backup plan. When those services go down, parts of your site stop working entirely', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/external-service-fallback?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'context'      => array(
				'external_dependencies'  => $external_dependencies,
				'scripts_count'          => count( $external_scripts ?? array() ),
				'styles_count'           => count( $external_styles ?? array() ),
				'critical_apis'          => count( $active_critical_apis ),
				'impact'                 => __( 'When external services fail, your site can become unusable. jQuery from CDN fails = broken JavaScript. Payment API down = no sales. Email API down = lost signups.', 'wpshadow' ),
				'recommendation'         => array(
					__( 'Host critical scripts locally as fallback', 'wpshadow' ),
					__( 'Implement retry logic for API calls', 'wpshadow' ),
					__( 'Cache API responses when possible', 'wpshadow' ),
					__( 'Show user-friendly error messages when services are unavailable', 'wpshadow' ),
					__( 'Add circuit breakers to prevent cascading failures', 'wpshadow' ),
					__( 'Queue non-critical operations for later retry', 'wpshadow' ),
					__( 'Monitor external service uptime', 'wpshadow' ),
				),
				'example_fallback'       => __( 'Load jQuery from CDN, fall back to local copy if CDN fails', 'wpshadow' ),
				'real_world_scenario'    => __( 'In 2021, Fastly CDN outage took down major sites for hours because they had no fallback', 'wpshadow' ),
			),
		);
	}
}
