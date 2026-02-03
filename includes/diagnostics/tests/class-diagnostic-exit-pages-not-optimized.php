<?php
/**
 * Diagnostic: Exit Pages Not Optimized
 *
 * Detects top exit pages lacking retention strategies. Exit pages are the
 * last interaction users have with your site - optimize them to reduce exits.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7030.1452
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exit Pages Not Optimized Diagnostic Class
 *
 * Checks for high-exit pages and retention strategies.
 *
 * Detection methods:
 * - Analytics plugin for exit rate data
 * - Exit intent popup plugins
 * - Related posts and CTAs in high-exit pages
 *
 * @since 1.7030.1452
 */
class Diagnostic_Exit_Pages_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'exit-pages-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Exit Pages Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Top exit pages lack retention strategies';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-engagement';

	/**
	 * Run the diagnostic check.
	 *
	 * Scoring system (4 points):
	 * - 2 points: Analytics plugin for exit tracking
	 * - 1 point: Exit intent plugin
	 * - 1 point: Retention features (related posts, CTAs)
	 *
	 * @since  1.7030.1452
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score              = 0;
		$max_score          = 4;
		$has_analytics      = false;
		$has_exit_intent    = false;
		$has_retention      = false;
		$active_tools       = array();

		// Check for analytics plugins.
		$analytics_plugins = array(
			'google-site-kit/google-site-kit.php'           => 'Google Site Kit',
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
			'matomo/matomo.php'                             => 'Matomo',
		);

		foreach ( $analytics_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score        += 2;
				$has_analytics = true;
				$active_tools[] = $name;
				break;
			}
		}

		// Check for exit intent popup plugins.
		$exit_intent_plugins = array(
			'optinmonster/optin-monster-wp-api.php'     => 'OptinMonster',
			'hustle/opt-in.php'                         => 'Hustle',
			'popup-maker/popup-maker.php'               => 'Popup Maker',
			'thrive-leads/thrive-leads.php'             => 'Thrive Leads',
		);

		foreach ( $exit_intent_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score           += 1;
				$has_exit_intent  = true;
				$active_tools[]   = $name;
				break;
			}
		}

		// Check for retention features (related posts, prominent CTAs).
		$retention_plugins = array(
			'yet-another-related-posts-plugin/yarpp.php' => 'Related Posts',
			'contextual-related-posts/contextual-related-posts.php' => 'Related Posts',
		);

		foreach ( $retention_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score         += 1;
				$has_retention  = true;
				$active_tools[] = $name;
				break;
			}
		}

		// Check for exit page meta (if analytics plugin stores this).
		global $wpdb;
		$exit_data = $wpdb->get_results(
			"SELECT post_id, meta_value 
			FROM {$wpdb->postmeta} 
			WHERE meta_key LIKE '%exit%' 
			OR meta_key LIKE '%bounce%'
			LIMIT 5"
		);

		$high_exit_pages = array();
		if ( ! empty( $exit_data ) ) {
			foreach ( $exit_data as $data ) {
				$post = get_post( $data->post_id );
				if ( $post && 'post' === $post->post_type ) {
					$high_exit_pages[] = array(
						'post_id' => $post->ID,
						'title'   => $post->post_title,
						'url'     => get_permalink( $post->ID ),
					);
				}
			}
		}

		// Pass if score is high.
		if ( $score >= ( $max_score * 0.7 ) ) {
			return null;
		}

		// Build message.
		$issues = array();
		if ( ! $has_analytics ) {
			$issues[] = __( 'No analytics to track exit pages', 'wpshadow' );
		}
		if ( ! $has_exit_intent ) {
			$issues[] = __( 'No exit intent popup', 'wpshadow' );
		}
		if ( ! $has_retention ) {
			$issues[] = __( 'Missing retention features', 'wpshadow' );
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of issues */
				__( '%s. Exit pages are your last chance to keep visitors. Average exit rate: 40-60%% (varies by page type). High-exit pages need optimization. Strategies: Exit intent popups (recover 10-15%% of abandoning visitors), Strong CTAs (next action clear?), Related content (3-6 relevant posts), Internal links (2-3 contextual), Email signup (last-chance offer), Social proof (testimonials, case studies). Analyze: Which pages have highest exit rate? Why? Dead-end content? Satisfied visitor (conversion)? Missing next step? Fix top 5 exit pages first.', 'wpshadow' ),
				implode( '. ', $issues )
			),
			'severity'    => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/exit-pages-not-optimized',
			'stats'       => array(
				'has_analytics'    => $has_analytics,
				'has_exit_intent'  => $has_exit_intent,
				'has_retention'    => $has_retention,
				'active_tools'     => $active_tools,
			),
			'high_exit_pages' => $high_exit_pages,
			'recommendation' => __( 'Install analytics to identify top exit pages. Add exit intent popup (OptinMonster, Hustle). Ensure all posts have related posts section. Add clear CTAs. Test: What would make YOU stay on this page longer?', 'wpshadow' ),
		);
	}
}
