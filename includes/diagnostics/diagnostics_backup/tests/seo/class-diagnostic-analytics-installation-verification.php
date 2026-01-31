<?php
/**
 * Analytics Installation Verification Diagnostic
 *
 * Confirms Google Analytics or tracking is installed and actively collecting data.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Analytics Installation Verification Class
 *
 * Tests analytics setup.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Analytics_Installation_Verification extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'analytics-installation-verification';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Analytics Installation Verification';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Confirms Google Analytics or tracking is installed and actively collecting data';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26029.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$analytics_check = self::check_analytics_installation();
		
		if ( ! $analytics_check['analytics_detected'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No analytics tracking detected (running without data insights)', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/analytics-installation-verification',
				'meta'         => array(
					'ga4_detected'         => $analytics_check['ga4_detected'],
					'gtm_detected'         => $analytics_check['gtm_detected'],
					'universal_detected'   => $analytics_check['universal_detected'],
					'plugin_detected'      => $analytics_check['plugin_detected'],
				),
			);
		}

		return null;
	}

	/**
	 * Check analytics installation.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_analytics_installation() {
		$check = array(
			'analytics_detected' => false,
			'ga4_detected'       => false,
			'gtm_detected'       => false,
			'universal_detected' => false,
			'plugin_detected'    => false,
		);

		// Check for common analytics plugins.
		$analytics_plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php',
			'google-analytics-dashboard-for-wp/gadwp.php',
			'google-site-kit/google-site-kit.php',
			'ga-google-analytics/ga-google-analytics.php',
			'googleanalytics/googleanalytics.php',
		);

		foreach ( $analytics_plugins as $plugin_file ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$check['plugin_detected'] = true;
				$check['analytics_detected'] = true;
				break;
			}
		}

		// Check homepage HTML for tracking codes.
		$response = wp_remote_get( home_url(), array(
			'timeout' => 10,
		) );

		if ( ! is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );

			// Check for GA4 (G-XXXXXXXXXX).
			if ( preg_match( '/G-[A-Z0-9]{10}/i', $body ) ) {
				$check['ga4_detected'] = true;
				$check['analytics_detected'] = true;
			}

			// Check for Google Tag Manager (GTM-XXXXXXX).
			if ( preg_match( '/GTM-[A-Z0-9]{7}/i', $body ) ) {
				$check['gtm_detected'] = true;
				$check['analytics_detected'] = true;
			}

			// Check for Universal Analytics (UA-XXXXXXXX-X).
			if ( preg_match( '/UA-\d{8}-\d/i', $body ) ) {
				$check['universal_detected'] = true;
				$check['analytics_detected'] = true;
			}

			// Check for gtag.js or analytics.js.
			if ( false !== strpos( $body, 'gtag.js' ) || 
			     false !== strpos( $body, 'analytics.js' ) ||
			     false !== strpos( $body, 'googletagmanager.com' ) ) {
				$check['analytics_detected'] = true;
			}
		}

		return $check;
	}
}
