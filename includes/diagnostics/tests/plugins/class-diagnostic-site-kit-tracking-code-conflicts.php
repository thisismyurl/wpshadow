<?php
/**
 * Site Kit Tracking Code Conflicts and Duplication Diagnostic
 *
 * Detects if Site Kit is duplicating tracking codes already installed manually.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26031.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site Kit Tracking Code Duplication Diagnostic Class
 *
 * Checks for duplicate tracking codes that can skew analytics data.
 *
 * @since 1.26031.1200
 */
class Diagnostic_Site_Kit_Tracking_Code_Conflicts extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'site-kit-tracking-code-conflicts';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Site Kit Tracking Code Conflicts and Duplication';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects if Site Kit duplicating tracking codes already installed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26031.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if Site Kit is active.
		if ( ! class_exists( 'Google\Site_Kit\Context' ) && ! class_exists( 'Google\Site_Kit\Plugin' ) ) {
			return null; // Plugin not active, no check needed.
		}

		$issues = array();

		// Get the home page HTML to check for tracking codes.
		$home_url  = home_url( '/' );
		$response  = wp_remote_get( $home_url );
		$html_body = '';

		if ( ! is_wp_error( $response ) ) {
			$html_body = wp_remote_retrieve_body( $response );
		}

		// Check for duplicate Google Analytics codes.
		$ga_count = preg_match_all( '/gtag\(|ga\(|GoogleAnalyticsObject/', $html_body );
		if ( $ga_count > 1 ) {
			$issues[] = array(
				'code_type'   => 'Google Analytics',
				'occurrences' => $ga_count,
				'description' => sprintf(
					/* translators: %d: number of tracking code instances */
					__( 'Google Analytics tracking code appears %d times on the page', 'wpshadow' ),
					$ga_count
				),
				'severity'    => 'high',
			);
		}

		// Check for both GA4 and Universal Analytics.
		$has_ga4 = preg_match( '/gtag\(\'config\',\s*["\']G-/', $html_body );
		$has_ua  = preg_match( '/gtag\(\'config\',\s*["\']UA-/', $html_body );
		if ( $has_ga4 && $has_ua ) {
			$issues[] = array(
				'code_type'   => 'Google Analytics',
				'description' => __( 'Both GA4 and Universal Analytics are active, causing duplicate tracking', 'wpshadow' ),
				'severity'    => 'high',
			);
		}

		// Check for duplicate Tag Manager containers.
		$gtm_count = preg_match_all( '/googletagmanager\.com\/gtm\.js\?id=GTM-/', $html_body );
		if ( $gtm_count > 1 ) {
			$issues[] = array(
				'code_type'   => 'Google Tag Manager',
				'occurrences' => $gtm_count,
				'description' => sprintf(
					/* translators: %d: number of Tag Manager instances */
					__( 'Google Tag Manager appears %d times on the page', 'wpshadow' ),
					$gtm_count
				),
				'severity'    => 'high',
			);
		}

		// Check for AdSense code duplication.
		$adsense_count = preg_match_all( '/pagead2\.googlesyndication\.com/', $html_body );
		if ( $adsense_count > 2 ) { // Allow up to 2 (script + auto ads).
			$issues[] = array(
				'code_type'   => 'Google AdSense',
				'occurrences' => $adsense_count,
				'description' => sprintf(
					/* translators: %d: number of AdSense script instances */
					__( 'AdSense scripts appear %d times, may indicate duplication', 'wpshadow' ),
					$adsense_count
				),
				'severity'    => 'medium',
			);
		}

		// Check for conflicting analytics plugins.
		$analytics_plugins = array(
			'MonsterInsights'       => class_exists( 'MonsterInsights' ),
			'ExactMetrics'          => class_exists( 'ExactMetrics' ),
			'GA Google Analytics'   => function_exists( 'ga_google_analytics_init' ),
			'Analytify'             => class_exists( 'WP_Analytify' ),
			'Google Analytics Pro'  => class_exists( 'GADWP' ) || class_exists( 'GAPWP' ),
		);

		$active_analytics_plugins = array_filter( $analytics_plugins );
		if ( count( $active_analytics_plugins ) > 0 ) {
			$issues[] = array(
				'code_type'   => 'Conflicting Plugins',
				'description' => sprintf(
					/* translators: %s: list of plugin names */
					__( 'Other analytics plugins are active alongside Site Kit: %s', 'wpshadow' ),
					implode( ', ', array_keys( $active_analytics_plugins ) )
				),
				'severity'    => 'medium',
			);
		}

		if ( empty( $issues ) ) {
			return null; // No issues found.
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of tracking code conflicts */
				__( 'Found %d tracking code conflicts or duplications', 'wpshadow' ),
				count( $issues )
			),
			'severity'     => 'high',
			'threat_level' => 85,
			'auto_fixable' => false,
			'details'      => $issues,
			'kb_link'      => 'https://wpshadow.com/kb/site-kit-tracking-code-conflicts',
		);
	}
}
