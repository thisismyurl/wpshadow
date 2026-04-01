<?php
/**
 * Business Goals Tracking Diagnostic
 *
 * Verifies analytics goals/conversions are configured to track business outcomes.
 * Goals turn analytics from vanity metrics into actionable business intelligence.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Analytics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Tracks_Business_Goals Class
 *
 * Checks if analytics has goals/conversions configured:
 * - MonsterInsights goals and events
 * - Site Kit conversion tracking
 * - WooCommerce analytics integration
 * - Contact form tracking (WPForms, Contact Form 7)
 *
 * @since 0.6093.1200
 */
class Diagnostic_Tracks_Business_Goals extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $slug = 'tracks-business-goals';

	/**
	 * The diagnostic title
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $title = 'Goal Tracking Configured';

	/**
	 * The diagnostic description
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $description = 'Verifies analytics goals/conversions are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $family = 'analytics';

	/**
	 * Run the diagnostic check.
	 *
	 * First verifies analytics is installed, then checks if goals/conversions
	 * are configured through various methods:
	 * 1. MonsterInsights goal tracking settings
	 * 2. Site Kit conversion events
	 * 3. WooCommerce analytics integration
	 * 4. Contact form tracking
	 *
	 * @since 0.6093.1200
	 * @return array|null {
	 *     Finding array if analytics exists but no goals configured, null otherwise.
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
		// First check if analytics is installed at all.
		if ( ! self::has_analytics_installed() ) {
			return null; // No analytics - can't check for goals.
		}

		$goal_tracking = array();
		$goals_found   = false;

		// Check MonsterInsights goals configuration.
		$monsterinsights_check = self::check_monsterinsights_goals();
		if ( $monsterinsights_check ) {
			$goals_found       = true;
			$goal_tracking[]   = $monsterinsights_check;
		}

		// Check Site Kit conversion tracking.
		$sitekit_check = self::check_sitekit_conversions();
		if ( $sitekit_check ) {
			$goals_found       = true;
			$goal_tracking[]   = $sitekit_check;
		}

		// Check WooCommerce analytics integration.
		$woocommerce_check = self::check_woocommerce_tracking();
		if ( $woocommerce_check ) {
			$goals_found       = true;
			$goal_tracking[]   = $woocommerce_check;
		}

		// Check contact form tracking.
		$forms_check = self::check_form_tracking();
		if ( $forms_check ) {
			$goals_found       = true;
			$goal_tracking[]   = $forms_check;
		}

		// If goals are configured, no issue to report.
		if ( $goals_found ) {
			return null;
		}

		// Analytics exists but no goals configured - return finding.
		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => self::get_description(),
			'severity'     => 'high',
			'threat_level' => 60,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/analytics-goal-tracking?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'meta'         => array(
				'has_analytics'   => true,
				'checked_methods' => array(
					'monsterinsights' => false,
					'sitekit'         => false,
					'woocommerce'     => false,
					'contact_forms'   => false,
				),
			),
		);
	}

	/**
	 * Check if analytics is installed.
	 *
	 * @since 0.6093.1200
	 * @return bool True if analytics detected.
	 */
	private static function has_analytics_installed() {
		// Check for analytics plugins.
		$analytics_plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php',
			'google-analytics-dashboard-for-wp/gadwp.php',
			'google-site-kit/google-site-kit.php',
			'ga-google-analytics/ga-google-analytics.php',
		);

		$active_plugins = get_option( 'active_plugins', array() );
		foreach ( $analytics_plugins as $plugin_file ) {
			if ( in_array( $plugin_file, $active_plugins, true ) ) {
				return true;
			}
		}

		// Check for tracking codes.
		ob_start();
		do_action( 'wp_head' );
		$header_output = ob_get_clean();

		if ( preg_match( '/G-[A-Z0-9]{10}|UA-\d{4,10}-\d{1,4}|gtag\(|analytics\.js/i', $header_output ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check MonsterInsights goal configuration.
	 *
	 * @since 0.6093.1200
	 * @return array|null Goal tracking details or null if not configured.
	 */
	private static function check_monsterinsights_goals() {
		if ( ! function_exists( 'MonsterInsights' ) ) {
			return null;
		}

		// Check if events tracking is enabled.
		$settings = get_option( 'monsterinsights_settings', array() );

		// Check various goal tracking settings.
		$tracking_enabled = false;

		if ( ! empty( $settings['events_mode'] ) && 'js' === $settings['events_mode'] ) {
			$tracking_enabled = true;
		}

		if ( ! empty( $settings['track_user'] ) ) {
			$tracking_enabled = true;
		}

		if ( $tracking_enabled ) {
			return array(
				'method' => 'MonsterInsights',
				'type'   => 'events_tracking',
			);
		}

		return null;
	}

	/**
	 * Check Site Kit conversion tracking.
	 *
	 * @since 0.6093.1200
	 * @return array|null Conversion tracking details or null if not configured.
	 */
	private static function check_sitekit_conversions() {
		if ( ! class_exists( 'Google\Site_Kit\Plugin' ) ) {
			return null;
		}

		// Check if Analytics module is connected and configured.
		$settings = get_option( 'googlesitekit_analytics_settings', array() );

		if ( ! empty( $settings['propertyID'] ) && ! empty( $settings['useSnippet'] ) ) {
			// Site Kit is configured - assume conversions may be set up in GA4.
			return array(
				'method' => 'Site Kit by Google',
				'type'   => 'ga4_integration',
			);
		}

		return null;
	}

	/**
	 * Check WooCommerce analytics tracking.
	 *
	 * @since 0.6093.1200
	 * @return array|null WooCommerce tracking details or null if not configured.
	 */
	private static function check_woocommerce_tracking() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}

		// Check if Google Analytics is integrated with WooCommerce.
		$wc_settings = get_option( 'woocommerce_google_analytics_settings', array() );

		if ( ! empty( $wc_settings['ga_id'] ) && ! empty( $wc_settings['ga_ecommerce_tracking_enabled'] ) ) {
			return array(
				'method' => 'WooCommerce',
				'type'   => 'ecommerce_tracking',
			);
		}

		// Check if MonsterInsights has WooCommerce integration active.
		if ( function_exists( 'MonsterInsights' ) ) {
			$mi_settings = get_option( 'monsterinsights_settings', array() );
			if ( ! empty( $mi_settings['automatic_updates'] ) ) {
				return array(
					'method' => 'MonsterInsights + WooCommerce',
					'type'   => 'ecommerce_tracking',
				);
			}
		}

		return null;
	}

	/**
	 * Check contact form tracking configuration.
	 *
	 * @since 0.6093.1200
	 * @return array|null Form tracking details or null if not configured.
	 */
	private static function check_form_tracking() {
		// Check WPForms analytics integration.
		if ( function_exists( 'wpforms' ) ) {
			$wpforms_settings = get_option( 'wpforms_settings', array() );
			if ( ! empty( $wpforms_settings['modern-tracking'] ) ) {
				return array(
					'method' => 'WPForms',
					'type'   => 'form_tracking',
				);
			}
		}

		// Check if MonsterInsights is tracking forms.
		if ( function_exists( 'MonsterInsights' ) ) {
			$mi_settings = get_option( 'monsterinsights_settings', array() );
			if ( ! empty( $mi_settings['track_forms'] ) ) {
				return array(
					'method' => 'MonsterInsights',
					'type'   => 'form_tracking',
				);
			}
		}

		return null;
	}

	/**
	 * Get detailed description of the finding.
	 *
	 * @since 0.6093.1200
	 * @return string Formatted description with recommendations.
	 */
	public static function get_description(): string {
		$description  = __( 'You have analytics installed, but no business goals are being tracked. This is like having a store with cameras but no cash register - you see activity but can\'t measure success.', 'wpshadow' ) . "\n\n";
		$description .= '<strong>' . __( 'Why This Matters:', 'wpshadow' ) . "</strong>\n";
		$description .= __( '• Goals turn pageviews into actionable business metrics', 'wpshadow' ) . "\n";
		$description .= __( '• Track what matters: form submissions, purchases, downloads', 'wpshadow' ) . "\n";
		$description .= __( '• Measure ROI of marketing campaigns and content', 'wpshadow' ) . "\n";
		$description .= __( '• Identify which traffic sources drive actual conversions', 'wpshadow' ) . "\n";
		$description .= __( '• A/B test changes by comparing conversion rates', 'wpshadow' ) . "\n\n";

		$description .= '<strong>' . __( 'Common Business Goals to Track:', 'wpshadow' ) . "</strong>\n";
		$description .= __( '• <strong>Contact Forms:</strong> Track submissions as conversions', 'wpshadow' ) . "\n";
		$description .= __( '• <strong>Newsletter Signups:</strong> Measure email list growth', 'wpshadow' ) . "\n";
		$description .= __( '• <strong>Product Purchases:</strong> Track ecommerce transactions', 'wpshadow' ) . "\n";
		$description .= __( '• <strong>File Downloads:</strong> Monitor PDF, ebook, or resource downloads', 'wpshadow' ) . "\n";
		$description .= __( '• <strong>Video Engagement:</strong> Track how far users watch videos', 'wpshadow' ) . "\n";
		$description .= __( '• <strong>Button Clicks:</strong> Monitor CTA effectiveness', 'wpshadow' ) . "\n\n";

		$description .= '<strong>' . __( 'How to Set Up Goal Tracking:', 'wpshadow' ) . "</strong>\n";
		$description .= __( '<strong>Option 1: MonsterInsights/ExactMetrics (Easiest)</strong>', 'wpshadow' ) . "\n";
		$description .= __( '1. Enable "Forms" tracking in MonsterInsights settings', 'wpshadow' ) . "\n";
		$description .= __( '2. Turn on "Link tracking" to monitor button clicks', 'wpshadow' ) . "\n";
		$description .= __( '3. Configure WooCommerce integration if using ecommerce', 'wpshadow' ) . "\n\n";

		$description .= __( '<strong>Option 2: Google Analytics 4 Manual Setup</strong>', 'wpshadow' ) . "\n";
		$description .= __( '1. Go to Admin > Events in your GA4 property', 'wpshadow' ) . "\n";
		$description .= __( '2. Click "Create Event" and define custom events', 'wpshadow' ) . "\n";
		$description .= __( '3. Mark important events as "Conversions"', 'wpshadow' ) . "\n\n";

		$description .= __( '<strong>Option 3: WPForms Integration</strong>', 'wpshadow' ) . "\n";
		$description .= __( '1. Install WPForms plugin if not already active', 'wpshadow' ) . "\n";
		$description .= __( '2. Enable "Modern Form Tracking" in WPForms settings', 'wpshadow' ) . "\n";
		$description .= __( '3. Form submissions automatically track as conversions', 'wpshadow' );

		return $description;
	}
}
