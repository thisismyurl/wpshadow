<?php
/**
 * Diagnostic: Data-Driven Content Decisions
 *
 * Tests if content topics are influenced by analytics and data.
 * Data-driven content strategy improves relevance and engagement.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Data-Driven Content Decisions Diagnostic Class
 *
 * Checks if site uses analytics data to inform content creation decisions.
 *
 * Detection methods:
 * - Analytics plugin installation
 * - Google Analytics/Tag Manager presence
 * - Search Console integration
 * - Recent content based on search trends
 *
 * @since 1.6093.1200
 */
class Diagnostic_Uses_Data_For_Content extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'uses-data-for-content';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Data-Driven Content Decisions';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if content topics are influenced by analytics and data';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'analytics';

	/**
	 * Run the diagnostic check.
	 *
	 * Scoring system (5 points):
	 * - 1 point: Analytics plugin installed
	 * - 1 point: Google Analytics configured
	 * - 1 point: Search Console connected
	 * - 1 point: Heat mapping/behavior tools
	 * - 1 point: Content updated based on performance data
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score     = 0;
		$max_score = 5;
		$details   = array();

		// Check for analytics plugins.
		$analytics_plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
			'google-site-kit/google-site-kit.php'                => 'Site Kit by Google',
			'google-analytics-dashboard-for-wp/gadwp.php'        => 'ExactMetrics',
			'jetpack/jetpack.php'                                => 'Jetpack',
			'matomo/matomo.php'                                  => 'Matomo Analytics',
		);

		$installed_analytics = array();
		foreach ( $analytics_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score++;
				$installed_analytics[] = $name;
			}
		}

		if ( ! empty( $installed_analytics ) ) {
			$details['analytics_plugins'] = $installed_analytics;
		}

		// Check for Google Analytics tracking code.
		$has_ga_code = false;
		if ( function_exists( 'get_option' ) ) {
			$ga_code = get_option( 'ga_code', '' );
			if ( ! empty( $ga_code ) || strpos( get_option( 'home' ), 'gtag' ) !== false ) {
				$score++;
				$has_ga_code = true;
			}
		}

		// Check for Site Kit connection.
		if ( class_exists( 'Google\Site_Kit\Plugin' ) ) {
			$score++;
			$details['site_kit_active'] = true;
		}

		// Check for heat mapping tools.
		$heatmap_plugins = array(
			'hotjar/hotjar.php'                  => 'Hotjar',
			'crazy-egg/crazy-egg.php'            => 'Crazy Egg',
			'mouseflow/mouseflow.php'            => 'Mouseflow',
			'lucky-orange/lucky-orange.php'      => 'Lucky Orange',
		);

		foreach ( $heatmap_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score++;
				$details['heatmap_tool'] = $name;
				break;
			}
		}

		// Check for recently updated posts (suggests data-driven updates).
		$recent_updates = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
				'orderby'        => 'modified',
				'order'          => 'DESC',
			)
		);

		$updated_count = 0;
		foreach ( $recent_updates as $post ) {
			// Check if modified date is significantly after publish date.
			$published = strtotime( $post->post_date );
			$modified  = strtotime( $post->post_modified );
			
			if ( $modified > $published + ( 7 * DAY_IN_SECONDS ) ) {
				$updated_count++;
			}
		}

		if ( $updated_count >= 3 ) {
			$score++;
			$details['updated_posts_count'] = $updated_count;
		}

		// Calculate percentage score.
		$percentage = ( $score / $max_score ) * 100;

		// Pass if score is 60% or higher.
		if ( $percentage >= 60 ) {
			return null;
		}

		// Build finding.
		$severity     = $percentage < 30 ? 'high' : 'medium';
		$threat_level = (int) ( 70 - $percentage );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: percentage score */
				__( 'Data-driven content strategy score: %d%%. Analytics and data should inform content decisions for better results.', 'wpshadow' ),
				(int) $percentage
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/data-driven-content',
			'details'      => $details,
			'why_matters'  => self::get_why_matters(),
		);
	}

	/**
	 * Get the "Why This Matters" educational content.
	 *
	 * @since 1.6093.1200
	 * @return string Explanation of why this diagnostic matters.
	 */
	private static function get_why_matters() {
		return __(
			'Data-driven content decisions help you create content that your audience actually wants. By analyzing search trends, user behavior, and engagement metrics, you can focus your efforts on topics that drive traffic and conversions. Without analytics, you\'re essentially guessing what your audience needs.',
			'wpshadow'
		);
	}
}
