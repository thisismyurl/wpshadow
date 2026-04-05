<?php
/**
 * Analytics Installed Diagnostic
 *
 * Without analytics a site owner has no visibility into traffic sources,
 * popular content, conversion paths, or user behaviour. This diagnostic
 * checks whether a recognised analytics solution is active and configured,
 * alerting when none is detected so the owner can make a deliberate choice.
 *
 * @package WPShadow
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
 * Diagnostic_Analytics_Installed_Intentional Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Analytics_Installed_Intentional extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'analytics-installed-intentional';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Analytics Installed';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks that an analytics solution (Google Analytics, Plausible, Matomo, etc.) is active so the site owner has visibility into traffic and user behaviour.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'monitoring';

	/**
	 * Whether this diagnostic is part of the core trusted set.
	 *
	 * @var bool
	 */
	protected static $is_core = true;

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'high';

	/**
	 * Active plugin file paths that provide analytics functionality.
	 * Maps plugin file → human-readable name.
	 */
	private const ANALYTICS_PLUGINS = array(
		// GA4 / Universal Analytics dedicated plugins.
		'google-analytics-for-wordpress/googleanalytics.php'         => 'MonsterInsights',
		'googleanalytics/googleanalytics.php'                        => 'Google Analytics Dashboard for WP',
		'wp-google-analytics-events/wp-google-analytics-events.php' => 'WP Google Analytics Events',
		'analytify-google-analytics-dashboard-for-wordpress/wp-analytify.php' => 'Analytify',
		'independent-analytics/independent-analytics.php'           => 'Independent Analytics',
		'google-site-kit/google-site-kit.php'                        => 'Site Kit by Google',
		// Privacy-focused / self-hosted alternatives.
		'plausible-analytics/plausible-analytics.php'               => 'Plausible Analytics',
		'fathom-analytics/fathom-analytics.php'                     => 'Fathom Analytics',
		'wp-statistics/wp-statistics.php'                           => 'WP Statistics',
		'matomo/matomo.php'                                         => 'Matomo Analytics',
		'wp-piwik/wp-piwik.php'                                     => 'Matomo Analytics (WP-Piwik)',
		'koko-analytics/koko-analytics.php'                         => 'Koko Analytics',
		// SEO suites that bundle analytics.
		'rankmath/rankmath.php'                                     => 'Rank Math SEO',
		'rank-math/rank-math.php'                                   => 'Rank Math SEO',
	);

	/**
	 * Option keys that hold a tracking ID when configured without a plugin.
	 */
	private const TRACKING_OPTIONS = array(
		'ga_google_analytics_id',
		'google_analytics_id',
		'analytify_profile_uids',
		'mgmt_tracking_id',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for active analytics plugins first, then falls back to
	 * checking common tracking-ID options. Returns null if any analytics
	 * solution is detected.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( array_keys( self::ANALYTICS_PLUGINS ) as $plugin_file ) {
			if ( is_plugin_active( $plugin_file ) ) {
				return null;
			}
		}

		// Check option-based tracking codes (e.g., injected via theme options).
		foreach ( self::TRACKING_OPTIONS as $option_key ) {
			$value = get_option( $option_key, '' );
			if ( '' !== trim( (string) $value ) ) {
				return null;
			}
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No analytics solution was detected on this site. Without analytics, traffic sources, popular content, bounce rates, and conversion paths are invisible, making data-driven decisions impossible.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 40,
			'kb_link'      => '',
			'details'      => array(
				'fix' => __( 'Install an analytics plugin. For privacy-friendly, GDPR-compliant tracking consider Plausible Analytics or Fathom. For full Google Analytics 4 integration, use Site Kit by Google or MonsterInsights. Ensure you update your Privacy Policy to disclose what data is collected.', 'wpshadow' ),
			),
		);
	}
}
