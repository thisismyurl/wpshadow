<?php
/**
 * Analytics Tracking Detection Diagnostic
 *
 * Verifies site has analytics tracking installed (GA4, MonsterInsights, etc.).
 * Analytics are essential for understanding user behavior and making data-driven decisions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Analytics
 * @since      1.6034.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Has_Analytics_Tracking Class
 *
 * Detects if the site has analytics tracking configured through:
 * - Google Analytics 4 (GA4) tracking code
 * - Popular analytics plugins (MonsterInsights, ExactMetrics, Site Kit)
 * - Universal Analytics (legacy UA- codes)
 * - Custom tracking implementations
 *
 * @since 1.6034.1200
 */
class Diagnostic_Has_Analytics_Tracking extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6034.1200
	 * @var   string
	 */
	protected static $slug = 'has-analytics-tracking';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6034.1200
	 * @var   string
	 */
	protected static $title = 'Analytics Tracking Installed';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6034.1200
	 * @var   string
	 */
	protected static $description = 'Verifies site has analytics tracking installed (GA4, MonsterInsights, etc.)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6034.1200
	 * @var   string
	 */
	protected static $family = 'analytics';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for analytics tracking through multiple methods:
	 * 1. Popular analytics plugins
	 * 2. Google Analytics 4 tracking code in header/footer
	 * 3. Universal Analytics (UA-) codes
	 * 4. Other analytics services (gtag, analytics.js)
	 *
	 * @since  1.6034.1200
	 * @return array|null {
	 *     Finding array if no analytics detected, null otherwise.
	 *
	 *     @type string $id           Diagnostic identifier.
	 *     @type string $title        Issue title.
	 *     @type string $description  Detailed description with recommendations.
	 *     @type string $severity     Issue severity level.
	 *     @type int    $threat_level Numeric threat level (0-100).
	 *     @type bool   $auto_fixable Whether issue can be auto-fixed.
	 *     @type string $kb_link      Link to knowledge base article.
	 *     @type array  $meta         Additional diagnostic data.
	 * }
	 */
	public static function check() {
		$detection_methods = array();
		$analytics_found   = false;

		// Check for popular analytics plugins.
		$plugin_check = self::check_analytics_plugins();
		if ( ! empty( $plugin_check ) ) {
			$analytics_found     = true;
			$detection_methods[] = $plugin_check;
		}

		// Check for Google Analytics tracking codes in header/footer.
		$tracking_code_check = self::check_tracking_codes();
		if ( ! empty( $tracking_code_check ) ) {
			$analytics_found     = true;
			$detection_methods[] = $tracking_code_check;
		}

		// If analytics found, no issue to report.
		if ( $analytics_found ) {
			return null;
		}

		// No analytics detected - return finding.
		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => self::get_description(),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/analytics-tracking',
			'meta'         => array(
				'checked_plugins'       => self::get_checked_plugin_list(),
				'checked_tracking_code' => true,
			),
		);
	}

	/**
	 * Check for analytics plugins.
	 *
	 * @since  1.6034.1200
	 * @return array|null Plugin detection details or null if none found.
	 */
	private static function check_analytics_plugins() {
		$analytics_plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
			'google-analytics-dashboard-for-wp/gadwp.php'        => 'ExactMetrics',
			'google-site-kit/google-site-kit.php'                => 'Site Kit by Google',
			'ga-google-analytics/ga-google-analytics.php'        => 'GA Google Analytics',
			'analytify-analytics-dashboard/wp-analytify.php'     => 'Analytify',
			'burst-statistics/burst.php'                         => 'Burst Statistics',
			'matomo/matomo.php'                                  => 'Matomo Analytics',
		);

		$active_plugins = get_option( 'active_plugins', array() );

		// Check single-site plugins.
		foreach ( $analytics_plugins as $plugin_file => $plugin_name ) {
			if ( in_array( $plugin_file, $active_plugins, true ) ) {
				return array(
					'type'   => 'plugin',
					'plugin' => $plugin_name,
					'file'   => $plugin_file,
				);
			}
		}

		// Check network-active plugins for multisite.
		if ( is_multisite() ) {
			$network_plugins = get_site_option( 'active_sitewide_plugins', array() );
			foreach ( $analytics_plugins as $plugin_file => $plugin_name ) {
				if ( array_key_exists( $plugin_file, $network_plugins ) ) {
					return array(
						'type'   => 'plugin',
						'plugin' => $plugin_name,
						'file'   => $plugin_file,
					);
				}
			}
		}

		return null;
	}

	/**
	 * Check for analytics tracking codes in header/footer.
	 *
	 * @since  1.6034.1200
	 * @return array|null Tracking code detection details or null if none found.
	 */
	private static function check_tracking_codes() {
		// Capture wp_head and wp_footer output.
		ob_start();
		do_action( 'wp_head' );
		$header_output = ob_get_clean();

		ob_start();
		do_action( 'wp_footer' );
		$footer_output = ob_get_clean();

		$combined_output = $header_output . $footer_output;

		// Check for Google Analytics 4 (GA4).
		if ( preg_match( '/G-[A-Z0-9]{10}/i', $combined_output, $matches ) ) {
			return array(
				'type'         => 'tracking_code',
				'service'      => 'Google Analytics 4',
				'tracking_id'  => $matches[0],
				'found_in'     => strpos( $header_output, $matches[0] ) !== false ? 'header' : 'footer',
			);
		}

		// Check for gtag.js (modern Google Analytics).
		if ( preg_match( '/gtag\(|googletagmanager\.com\/gtag/i', $combined_output ) ) {
			return array(
				'type'     => 'tracking_code',
				'service'  => 'Google Analytics (gtag.js)',
				'found_in' => strpos( $header_output, 'gtag' ) !== false ? 'header' : 'footer',
			);
		}

		// Check for Universal Analytics (UA-).
		if ( preg_match( '/UA-\d{4,10}-\d{1,4}/i', $combined_output, $matches ) ) {
			return array(
				'type'         => 'tracking_code',
				'service'      => 'Google Analytics (Universal)',
				'tracking_id'  => $matches[0],
				'found_in'     => strpos( $header_output, $matches[0] ) !== false ? 'header' : 'footer',
			);
		}

		// Check for analytics.js (legacy).
		if ( preg_match( '/google-analytics\.com\/analytics\.js/i', $combined_output ) ) {
			return array(
				'type'     => 'tracking_code',
				'service'  => 'Google Analytics (analytics.js)',
				'found_in' => strpos( $header_output, 'analytics.js' ) !== false ? 'header' : 'footer',
			);
		}

		// Check for Matomo/Piwik.
		if ( preg_match( '/matomo\.js|piwik\.js|_paq\.push/i', $combined_output ) ) {
			return array(
				'type'     => 'tracking_code',
				'service'  => 'Matomo/Piwik Analytics',
				'found_in' => strpos( $header_output, 'matomo' ) !== false ? 'header' : 'footer',
			);
		}

		return null;
	}

	/**
	 * Get list of checked plugins.
	 *
	 * @since  1.6034.1200
	 * @return array List of plugin names checked.
	 */
	private static function get_checked_plugin_list() {
		return array(
			'MonsterInsights',
			'ExactMetrics',
			'Site Kit by Google',
			'GA Google Analytics',
			'Analytify',
			'Burst Statistics',
			'Matomo Analytics',
		);
	}

	/**
	 * Get detailed description of the finding.
	 *
	 * @since  1.6034.1200
	 * @return string Formatted description with recommendations.
	 */
	private static function get_description() {
		$description  = __( 'No analytics tracking was detected on your site. Without analytics, you\'re flying blind - making decisions based on gut feeling instead of data.', 'wpshadow' ) . "\n\n";
		$description .= '<strong>' . __( 'Why This Matters:', 'wpshadow' ) . "</strong>\n";
		$description .= __( '• You can\'t improve what you don\'t measure', 'wpshadow' ) . "\n";
		$description .= __( '• Analytics reveal which content resonates with your audience', 'wpshadow' ) . "\n";
		$description .= __( '• Track where traffic comes from and optimize marketing efforts', 'wpshadow' ) . "\n";
		$description .= __( '• Identify technical issues (broken links, slow pages) before they hurt SEO', 'wpshadow' ) . "\n";
		$description .= __( '• Understand user behavior to improve conversions', 'wpshadow' ) . "\n\n";

		$description .= '<strong>' . __( 'What You\'re Missing Without Analytics:', 'wpshadow' ) . "</strong>\n";
		$description .= __( '• Which pages get the most traffic', 'wpshadow' ) . "\n";
		$description .= __( '• How long visitors stay on your site', 'wpshadow' ) . "\n";
		$description .= __( '• Which traffic sources drive the most conversions', 'wpshadow' ) . "\n";
		$description .= __( '• Where users drop off in your conversion funnel', 'wpshadow' ) . "\n";
		$description .= __( '• Mobile vs. desktop performance', 'wpshadow' ) . "\n\n";

		$description .= '<strong>' . __( 'Recommended Solutions:', 'wpshadow' ) . "</strong>\n";
		$description .= __( '1. <strong>Free Option:</strong> Install Site Kit by Google (official Google plugin)', 'wpshadow' ) . "\n";
		$description .= __( '2. <strong>User-Friendly:</strong> MonsterInsights or ExactMetrics (WordPress dashboard integration)', 'wpshadow' ) . "\n";
		$description .= __( '3. <strong>Privacy-Focused:</strong> Matomo or Burst Statistics (no data to third parties)', 'wpshadow' ) . "\n";
		$description .= __( '4. <strong>Manual Setup:</strong> Add Google Analytics 4 tracking code directly', 'wpshadow' ) . "\n\n";

		$description .= '<strong>' . __( 'Quick Setup Guide:', 'wpshadow' ) . "</strong>\n";
		$description .= __( '1. Create a Google Analytics 4 property at analytics.google.com', 'wpshadow' ) . "\n";
		$description .= __( '2. Install Site Kit plugin or paste tracking code in your theme', 'wpshadow' ) . "\n";
		$description .= __( '3. Wait 24-48 hours for data to accumulate', 'wpshadow' ) . "\n";
		$description .= __( '4. Set up goals to track important actions (form submissions, purchases)', 'wpshadow' );

		return $description;
	}
}
