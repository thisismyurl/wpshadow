<?php
/**
 * Google Analytics Configuration Diagnostic
 *
 * Verifies Google Analytics properly installed and tracking,
 * preventing loss of critical visitor data and insights.
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
 * Diagnostic_Google_Analytics_Config Class
 *
 * Verifies Google Analytics tracking.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Google_Analytics_Config extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'google-analytics-config';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Google Analytics Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies Google Analytics tracking';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if GA not configured, null otherwise.
	 */
	public static function check() {
		$ga_status = self::check_google_analytics();

		if ( $ga_status['is_installed'] ) {
			return null; // GA properly configured
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Google Analytics not detected. No visitor data = blind to traffic sources, user behavior, conversions. Can\'t optimize what you can\'t measure.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/google-analytics',
			'family'       => self::$family,
			'meta'         => array(
				'tracking_detected' => $ga_status['method'],
			),
			'details'      => array(
				'why_analytics_matter'        => array(
					__( 'Track visitor count, sources (Google, social, direct)' ),
					__( 'Understand user behavior (which pages viewed, time on site)' ),
					__( 'Measure conversions (purchases, signups, form submissions)' ),
					__( 'Identify high-performing content' ),
					__( 'Data-driven decisions vs guessing' ),
				),
				'ga4_vs_universal_analytics'  => array(
					'Universal Analytics (GA3)' => array(
						'Deprecated: Stopped collecting data July 1, 2023',
						'Tracking ID: UA-XXXXXXXXX-X',
						'DO NOT USE: Data lost forever',
					),
					'Google Analytics 4 (GA4)' => array(
						'Current: Only supported version',
						'Measurement ID: G-XXXXXXXXXX',
						'Event-based tracking (not session-based)',
						'Better privacy controls (GDPR compliant)',
					),
				),
				'setting_up_google_analytics' => array(
					'Create GA4 Property' => array(
						'Go to: analytics.google.com',
						'Admin → Create Property',
						'Property name: Your Site Name',
						'Copy Measurement ID: G-XXXXXXXXXX',
					),
					'Install via Plugin (Recommended)' => array(
						'Plugin: Site Kit by Google (official)',
						'Setup: Connect Google account',
						'Auto-installs: GA4, Search Console, AdSense',
						'Dashboard: View analytics in WordPress',
					),
					'Manual Installation' => array(
						'Header.php: Add tracking code before </head>',
						'Code: <!-- Global site tag (gtag.js) - Google Analytics -->',
						'Or use: Google Tag Manager container',
					),
				),
				'verifying_ga_tracking'       => array(
					'Real-time Reports' => array(
						'analytics.google.com → Reports → Realtime',
						'Visit your site',
						'Should see: 1 active user',
					),
					'Browser Extension' => array(
						'Install: Google Tag Assistant',
						'Visit site: Green checkmark = working',
						'Red X = not working',
					),
					'View Page Source' => array(
						'Right-click → View Page Source',
						'Search: "gtag" or "G-"',
						'Should find: gtag(\'config\', \'G-XXXXXXXXXX\')',
					),
				),
				'common_ga_mistakes'          => array(
					'Tracking Admin Traffic' => array(
						'Problem: Your own visits inflate stats',
						'Fix: Install "Monster Insights" → Exclude logged-in users',
					),
					'Multiple Tracking Codes' => array(
						'Problem: Plugin + manual code = duplicate data',
						'Fix: Remove one, keep only plugin or manual',
					),
					'GDPR Cookie Consent' => array(
						'Problem: EU visitors not tracked without consent',
						'Fix: Cookie consent banner + delayed tracking',
					),
				),
				'key_metrics_to_monitor'      => array(
					__( 'Users: Total visitors over time' ),
					__( 'Sessions: Number of visits' ),
					__( 'Bounce Rate: % leaving after 1 page (target: <40%)' ),
					__( 'Average Session Duration: Time on site (target: >2min)' ),
					__( 'Top Pages: Most visited content' ),
					__( 'Traffic Sources: Organic, direct, referral, social' ),
					__( 'Conversions: Goal completions, e-commerce transactions' ),
				),
			),
		);
	}

	/**
	 * Check Google Analytics.
	 *
	 * @since  1.2601.2148
	 * @return array GA status.
	 */
	private static function check_google_analytics() {
		// Check for Site Kit plugin (official Google plugin)
		if ( is_plugin_active( 'google-site-kit/google-site-kit.php' ) ) {
			return array(
				'is_installed' => true,
				'method'       => 'Site Kit by Google',
			);
		}

		// Check for MonsterInsights
		if ( is_plugin_active( 'google-analytics-for-wordpress/googleanalytics.php' ) ) {
			return array(
				'is_installed' => true,
				'method'       => 'MonsterInsights',
			);
		}

		// Check for GA Google Analytics
		if ( is_plugin_active( 'ga-google-analytics/ga-google-analytics.php' ) ) {
			return array(
				'is_installed' => true,
				'method'       => 'GA Google Analytics',
			);
		}

		// Check homepage for GA tracking code
		$homepage = wp_remote_get( home_url() );
		if ( ! is_wp_error( $homepage ) ) {
			$body = wp_remote_retrieve_body( $homepage );
			if ( strpos( $body, 'gtag' ) !== false || strpos( $body, 'G-' ) !== false || strpos( $body, 'UA-' ) !== false ) {
				return array(
					'is_installed' => true,
					'method'       => 'Manual installation',
				);
			}
		}

		return array(
			'is_installed' => false,
			'method'       => 'Not detected',
		);
	}
}
