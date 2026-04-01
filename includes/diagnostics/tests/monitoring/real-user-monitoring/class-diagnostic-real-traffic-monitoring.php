<?php
/**
 * Real Traffic Monitoring Diagnostic
 *
 * Checks if real user session data is being collected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Real Traffic Monitoring Diagnostic Class
 *
 * Verifies real user traffic patterns are being tracked.
 * Like watching how customers move through your store.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Real_Traffic_Monitoring extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'real-traffic-monitoring';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Real Traffic Monitoring';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if real user session data is being collected';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'real-user-monitoring';

	/**
	 * Run the real traffic monitoring diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if monitoring issues detected, null otherwise.
	 */
	public static function check() {
		// Check for analytics tools.
		$analytics_tools = array(
			'Google Analytics'    => self::has_google_analytics(),
			'Jetpack Stats'       => class_exists( 'Jetpack' ) && method_exists( 'Jetpack', 'is_module_active' ) && \Jetpack::is_module_active( 'stats' ),
			'MonsterInsights'     => defined( 'MONSTERINSIGHTS_VERSION' ),
			'Site Kit'            => defined( 'GOOGLESITEKIT_VERSION' ),
			'Matomo'              => class_exists( 'WpMatomo' ) || defined( 'MATOMO_ANALYTICS_FILE' ),
			'Clicky'              => function_exists( 'clicky_admin_head' ),
		);

		$active_analytics = array();
		foreach ( $analytics_tools as $name => $detected ) {
			if ( $detected ) {
				$active_analytics[] = $name;
			}
		}

		if ( empty( $active_analytics ) ) {
			return array(
				'id'           => self::$slug . '-not-configured',
				'title'        => __( 'Real Traffic Not Being Monitored', 'wpshadow' ),
				'description'  => __( 'You\'re not tracking real visitor behavior on your site (like running a store without knowing how many customers visit or what they buy). Without analytics, you\'re making decisions blind—you don\'t know which content works, where visitors come from, or why they leave. Set up Google Analytics 4 (free) or Jetpack Stats to understand your visitors. This data helps you improve what matters and stop wasting time on what doesn\'t.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/setup-analytics?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(),
			);
		}

		// Check if analytics is actually receiving data (not just installed).
		$recent_traffic = get_transient( 'wpshadow_recent_analytics_data' );

		if ( false === $recent_traffic ) {
			return array(
				'id'           => self::$slug . '-no-recent-data',
				'title'        => __( 'Analytics Not Receiving Data', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: analytics tool name */
					__( 'You have %s installed, but it\'s not receiving recent traffic data (like having security cameras that aren\'t recording). This usually means: tracking code isn\'t properly installed, ad blockers are filtering it, or configuration is incomplete. Check your analytics dashboard—if you see data there, ignore this message. If not, verify your tracking code is in the header and test with an incognito browser window.', 'wpshadow' ),
					implode( ', ', $active_analytics )
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/troubleshoot-analytics?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'analytics_tools' => $active_analytics,
				),
			);
		}

		// Check if Event Tracking is configured (more than just pageviews).
		$event_tracking = get_option( 'wpshadow_event_tracking_configured', false );

		if ( ! $event_tracking ) {
			return array(
				'id'           => self::$slug . '-basic-only',
				'title'        => __( 'Only Basic Pageview Tracking Configured', 'wpshadow' ),
				'description'  => __( 'Your analytics only tracks pageviews, not user interactions (like counting people entering a store but not what they do inside). Event tracking shows you: button clicks, form submissions, video plays, downloads, outbound links. These insights reveal how visitors actually use your site, not just which pages they visit. Enable enhanced measurement in Google Analytics 4 or add event tracking to your analytics setup.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/event-tracking?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'analytics_tools' => $active_analytics,
				),
			);
		}

		return null; // Real traffic monitoring is properly configured.
	}

	/**
	 * Check if Google Analytics is present.
	 *
	 * @since 0.6093.1200
	 * @return bool True if GA detected.
	 */
	private static function has_google_analytics() {
		$head_content = get_transient( 'wpshadow_head_content_sample' );

		if ( false === $head_content ) {
			ob_start();
			wp_head();
			$head_content = ob_get_clean();
			set_transient( 'wpshadow_head_content_sample', $head_content, DAY_IN_SECONDS );
		}

		// Check for GA4, Universal Analytics, or GTM.
		return ( false !== strpos( $head_content, 'gtag' )
			|| false !== strpos( $head_content, 'analytics.js' )
			|| false !== strpos( $head_content, 'gtm.js' )
			|| false !== strpos( $head_content, 'G-' ) // GA4 measurement ID
			|| false !== strpos( $head_content, 'UA-' ) // Universal Analytics
		);
	}
}
