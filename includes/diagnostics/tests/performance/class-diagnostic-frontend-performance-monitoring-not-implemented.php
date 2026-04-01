<?php
/**
 * Frontend Performance Monitoring Not Implemented Diagnostic
 *
 * Checks frontend monitoring.
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
 * Diagnostic_Frontend_Performance_Monitoring_Not_Implemented Class
 *
 * Performs diagnostic check for Frontend Performance Monitoring Not Implemented.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Frontend_Performance_Monitoring_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'frontend-performance-monitoring-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Frontend Performance Monitoring Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks frontend monitoring';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for frontend performance monitoring tools.
		$monitoring_plugins = array(
			'query-monitor/query-monitor.php'           => 'Query Monitor',
			'wp-performance-profiler/wp-performance-profiler.php' => 'WP Performance Profiler',
		);

		$monitoring_detected = false;
		$monitoring_name     = '';

		foreach ( $monitoring_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$monitoring_detected = true;
				$monitoring_name     = $name;
				break;
			}
		}

		// Check for Google Analytics or similar.
		$has_analytics = is_plugin_active( 'google-analytics-for-wordpress/googleanalytics.php' ) ||
		                 is_plugin_active( 'google-analytics-dashboard-for-wp/gadwp.php' );

		// Check for real user monitoring (RUM) services.
		$has_rum = false;
		if ( function_exists( 'wp_head' ) ) {
			ob_start();
			do_action( 'wp_head' );
			$head_content = ob_get_clean();

			// Check for common RUM services.
			if ( strpos( $head_content, 'newrelic' ) !== false ||
			     strpos( $head_content, 'speedcurve' ) !== false ||
			     strpos( $head_content, 'web-vitals' ) !== false ) {
				$has_rum = true;
			}
		}

		// If no monitoring tools detected.
		if ( ! $monitoring_detected && ! $has_rum ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Frontend performance monitoring not implemented. You can\'t measure Core Web Vitals (LCP, FID, CLS) or real user performance. Install Query Monitor for development monitoring, or use Google Search Console for production Core Web Vitals tracking. Frontend performance affects SEO rankings (Google Page Experience update).', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/frontend-performance-monitoring?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'monitoring_detected' => false,
					'has_analytics'       => $has_analytics,
					'has_rum'             => false,
					'recommendation'      => __( 'DEVELOPMENT: Install Query Monitor (free, 300K+ installs) to track queries, hooks, and performance during development. PRODUCTION: Use Google Search Console (free) to monitor Core Web Vitals from real users. ADVANCED: New Relic or SpeedCurve for detailed RUM analytics.', 'wpshadow' ),
					'core_web_vitals'     => array(
						'lcp' => 'Largest Contentful Paint: Main content load time (<2.5s good)',
						'fid' => 'First Input Delay: Interactivity delay (<100ms good)',
						'cls' => 'Cumulative Layout Shift: Visual stability (<0.1 good)',
						'seo_impact' => 'Google uses Core Web Vitals as ranking factor',
					),
					'monitoring_tools'    => array(
						'query_monitor' => 'WordPress plugin: Development monitoring',
						'google_search_console' => 'Free: Real user Core Web Vitals',
						'pagespeed_insights' => 'Free: Google\'s performance analysis',
						'new_relic' => 'Paid: Advanced RUM with detailed metrics',
						'speedcurve' => 'Paid: Performance budgets and alerts',
					),
					'why_it_matters'      => array(
						'seo_rankings' => 'Slow sites rank lower in Google',
						'user_experience' => 'Users abandon slow sites (53% at 3+ seconds)',
						'conversions' => '1 second delay = 7% conversion drop',
						'competitive_advantage' => 'Faster sites win more traffic',
					),
				),
			);
		}

		// No issues - monitoring implemented.
		return null;
	}
}
