<?php
/**
 * Noindex Tag on Valuable Content Diagnostic
 *
 * Detects noindex meta tags on high-value pages that should be indexed,
 * preventing valuable content from appearing in search results.
 *
 * @since   1.6028.1530
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Noindex_Valuable_Content Class
 *
 * Checks for accidental noindex tags on important pages that should
 * be visible in search engines.
 *
 * @since 1.6028.1530
 */
class Diagnostic_Noindex_Valuable_Content extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'noindex-valuable-content';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Noindex Tag on Valuable Content';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects noindex meta tags on high-traffic pages, preventing valuable content from ranking';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * Scans site for noindex tags and checks if they're on important content
	 * that should be indexed.
	 *
	 * @since  1.6028.1530
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check site-wide noindex setting first (most critical)
		if ( ! get_option( 'blog_public' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Your entire site is set to discourage search engines from indexing. This is a site-wide noindex setting preventing all content from appearing in search results.', 'wpshadow' ),
				'severity'      => 'critical',
				'threat_level'  => 95,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/search-engine-visibility',
				'family'        => self::$family,
				'meta'          => array(
					'setting_location'  => 'Settings > Reading > Search Engine Visibility',
					'affects'           => 'Entire site',
					'impact_level'      => __( 'Critical - Complete SEO blockage', 'wpshadow' ),
					'immediate_actions' => array(
						__( 'Go to Settings > Reading', 'wpshadow' ),
						__( 'Uncheck "Discourage search engines from indexing this site"', 'wpshadow' ),
						__( 'Save changes immediately', 'wpshadow' ),
						__( 'Request re-indexing in Google Search Console', 'wpshadow' ),
					),
				),
				'details'       => array(
					'why_important'    => __( 'This setting adds a "noindex, nofollow" meta tag to every page on your site, completely blocking search engine indexing. This is typically enabled during development and accidentally left on after launch. It results in zero organic traffic from search engines.', 'wpshadow' ),
					'user_impact'      => array(
						__( 'Zero organic traffic from Google, Bing, etc.', 'wpshadow' ),
						__( 'Existing rankings drop and disappear within days', 'wpshadow' ),
						__( 'Pages removed from search engine indexes', 'wpshadow' ),
						__( 'Complete loss of SEO value and visibility', 'wpshadow' ),
					),
					'solution_options' => array(
						'Fix Immediately' => array(
							'description' => __( 'Disable search engine discouragement', 'wpshadow' ),
							'time'        => __( '1 minute', 'wpshadow' ),
							'cost'        => __( 'Free', 'wpshadow' ),
							'difficulty'  => __( 'Easy', 'wpshadow' ),
							'steps'       => array(
								__( 'Login to WordPress admin', 'wpshadow' ),
								__( 'Go to Settings > Reading', 'wpshadow' ),
								__( 'Uncheck "Discourage search engines from indexing this site"', 'wpshadow' ),
								__( 'Click Save Changes', 'wpshadow' ),
								__( 'Verify fix: View source of homepage, search for "noindex" (should be gone)', 'wpshadow' ),
							),
						),
					),
					'recovery_timeline' => array(
						__( 'Immediate: Fix takes 1 minute', 'wpshadow' ),
						__( '24-48 hours: Google discovers change', 'wpshadow' ),
						__( '1-2 weeks: Pages start re-appearing in results', 'wpshadow' ),
						__( '2-4 weeks: Rankings begin to recover', 'wpshadow' ),
						__( 'Submit sitemap to Search Console to speed recovery', 'wpshadow' ),
					),
				),
			);
		}

		// Check for plugin-based noindex settings
		$plugin_noindex = self::check_plugin_noindex_settings();

		if ( ! empty( $plugin_noindex ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: number of content types with noindex */
					_n(
						'%d content type is set to noindex by your SEO plugin, potentially hiding valuable content.',
						'%d content types are set to noindex by your SEO plugin, potentially hiding valuable content.',
						count( $plugin_noindex ),
						'wpshadow'
					),
					count( $plugin_noindex )
				),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/noindex-settings',
				'family'        => self::$family,
				'meta'          => array(
					'noindexed_types'   => $plugin_noindex,
					'impact_level'      => __( 'High - Important content hidden from search', 'wpshadow' ),
					'immediate_actions' => array(
						__( 'Review SEO plugin settings (Yoast/RankMath)', 'wpshadow' ),
						__( 'Enable indexing for valuable content types', 'wpshadow' ),
						__( 'Check Search Console for indexation drops', 'wpshadow' ),
					),
				),
				'details'       => array(
					'why_important'    => __( 'SEO plugins allow setting noindex on entire post types, taxonomies, or archives. While useful for thin content (tags, author archives), accidentally noindexing posts or important categories causes massive traffic loss.', 'wpshadow' ),
					'solution_options' => array(
						'Yoast SEO' => array(
							'description' => __( 'Check Search Appearance settings', 'wpshadow' ),
							'steps'       => array(
								__( 'Go to Yoast SEO > Search Appearance', 'wpshadow' ),
								__( 'Check Content Types tab', 'wpshadow' ),
								__( 'Verify "Show X in search results" is ON for important content', 'wpshadow' ),
								__( 'Check Taxonomies tab for categories/tags', 'wpshadow' ),
								__( 'Save changes', 'wpshadow' ),
							),
						),
						'RankMath' => array(
							'description' => __( 'Check Titles & Meta settings', 'wpshadow' ),
							'steps'       => array(
								__( 'Go to Rank Math > Titles & Meta', 'wpshadow' ),
								__( 'Check each post type tab', 'wpshadow' ),
								__( 'Verify "Robots Meta" is set to Index', 'wpshadow' ),
								__( 'Check Taxonomies section', 'wpshadow' ),
							),
						),
					),
				),
			);
		}

		return null; // No problematic noindex found
	}

	/**
	 * Check SEO plugin noindex settings.
	 *
	 * @since  1.6028.1530
	 * @return array Content types with noindex enabled.
	 */
	private static function check_plugin_noindex_settings() {
		$noindexed = array();

		// Check Yoast SEO settings
		if ( defined( 'WPSEO_VERSION' ) ) {
			$important_types = array( 'post', 'page' );
			foreach ( $important_types as $type ) {
				$option = get_option( 'wpseo_titles' );
				if ( isset( $option[ 'noindex-' . $type ] ) && $option[ 'noindex-' . $type ] ) {
					$noindexed[] = $type;
				}
			}
		}

		// Check RankMath settings
		if ( defined( 'RANK_MATH_VERSION' ) ) {
			$important_types = array( 'post', 'page' );
			foreach ( $important_types as $type ) {
				$robots = get_option( "rank_math_pt_{$type}_robots" );
				if ( $robots && in_array( 'noindex', $robots, true ) ) {
					$noindexed[] = $type;
				}
			}
		}

		return $noindexed;
	}
}
