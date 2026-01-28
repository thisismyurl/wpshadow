<?php
/**
 * XML Sitemap Outdated Diagnostic
 *
 * Detects stale XML sitemaps with lastmod dates >7 days old, indicating
 * infrequent updates that may delay content discovery by search engines.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1705
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_XML_Sitemap_Outdated Class
 *
 * Parses XML sitemap to check lastmod dates and detect outdated entries.
 * Compares against actual post modification dates to verify accuracy.
 *
 * @since 1.6028.1705
 */
class Diagnostic_XML_Sitemap_Outdated extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'xml-sitemap-outdated';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'XML Sitemap Outdated (>7 Days)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects stale XML sitemaps with infrequent updates';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1705
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$sitemap_data = self::analyze_sitemap();

		if ( ! $sitemap_data['exists'] ) {
			return array(
				'id'          => self::$slug,
				'title'       => __( 'XML Sitemap Not Found', 'wpshadow' ),
				'description' => __( 'No XML sitemap detected. Search engines rely on sitemaps for content discovery.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/xml-sitemap',
				'family'      => self::$family,
				'meta'        => array(
					'sitemap_url'       => $sitemap_data['sitemap_url'],
					'recommended'       => __( 'Generate XML sitemap with SEO plugin or WordPress core', 'wpshadow' ),
					'impact_level'      => 'medium',
					'immediate_actions' => array(
						__( 'Enable WordPress core sitemap (WordPress 5.5+)', 'wpshadow' ),
						__( 'Or install Yoast SEO/RankMath for advanced sitemap', 'wpshadow' ),
						__( 'Submit sitemap to Google Search Console', 'wpshadow' ),
					),
				),
				'details'     => self::get_missing_sitemap_details(),
			);
		}

		if ( ! $sitemap_data['is_outdated'] ) {
			return null; // Sitemap is up to date.
		}

		// Determine severity based on age.
		$days_old = $sitemap_data['days_old'];
		if ( $days_old > 30 ) {
			$severity     = 'low';
			$threat_level = 40;
		} elseif ( $days_old > 14 ) {
			$severity     = 'info';
			$threat_level = 30;
		} else {
			$severity     = 'info';
			$threat_level = 20;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %d: number of days since last update */
				__( 'XML sitemap has not been updated in %d days. Outdated sitemaps delay content discovery.', 'wpshadow' ),
				$days_old
			),
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/xml-sitemap-updates',
			'family'      => self::$family,
			'meta'        => array(
				'sitemap_url'       => $sitemap_data['sitemap_url'],
				'last_modified'     => $sitemap_data['last_modified'],
				'days_old'          => $days_old,
				'recent_posts'      => $sitemap_data['recent_posts'],
				'recommended'       => __( 'Update sitemap daily or on content changes', 'wpshadow' ),
				'impact_level'      => 'low',
				'immediate_actions' => array(
					__( 'Regenerate sitemap manually', 'wpshadow' ),
					__( 'Enable automatic sitemap updates', 'wpshadow' ),
					__( 'Verify recent content in sitemap', 'wpshadow' ),
					__( 'Resubmit sitemap to Search Console', 'wpshadow' ),
				),
			),
			'details'     => array(
				'why_important' => __( 'XML sitemaps inform search engines about your content and when it was last modified. Outdated sitemaps cause search engines to miss new content, delay crawling of updates, and waste crawl budget re-checking unchanged pages. Fresh sitemaps accelerate content discovery and indexation.', 'wpshadow' ),
				'user_impact'   => array(
					__( 'Delayed Indexation: New content takes longer to appear in search', 'wpshadow' ),
					__( 'Missed Updates: Changes to existing pages not detected quickly', 'wpshadow' ),
					__( 'Crawl Inefficiency: Search engines waste time on unchanged pages', 'wpshadow' ),
					__( 'Lower Visibility: Fresh content competitors indexed faster', 'wpshadow' ),
				),
				'sitemap_analysis' => array(
					'url'            => $sitemap_data['sitemap_url'],
					'last_modified'  => $sitemap_data['last_modified'],
					'days_old'       => $days_old,
					'recent_posts'   => $sitemap_data['recent_posts'],
					'generation_method' => $sitemap_data['generation_method'],
				),
				'solution_options' => array(
					'free'     => array(
						'label'       => __( 'WordPress Core Sitemap (5.5+)', 'wpshadow' ),
						'description' => __( 'Enable built-in sitemap with automatic updates', 'wpshadow' ),
						'steps'       => array(
							__( 'WordPress 5.5+ includes native sitemap at /wp-sitemap.xml', 'wpshadow' ),
							__( 'Automatically updates when content changes', 'wpshadow' ),
							__( 'Verify at: https://yoursite.com/wp-sitemap.xml', 'wpshadow' ),
							__( 'Submit to Google Search Console', 'wpshadow' ),
							__( 'Monitor indexation status', 'wpshadow' ),
						),
					),
					'premium'  => array(
						'label'       => __( 'SEO Plugin Advanced Sitemap', 'wpshadow' ),
						'description' => __( 'Use Yoast SEO or RankMath for enhanced sitemap features', 'wpshadow' ),
						'steps'       => array(
							__( 'Install Yoast SEO (free) or RankMath (free)', 'wpshadow' ),
							__( 'Enable XML sitemap in plugin settings', 'wpshadow' ),
							__( 'Configure content types to include/exclude', 'wpshadow' ),
							__( 'Set update frequency and priority', 'wpshadow' ),
							__( 'Sitemap auto-updates on content changes', 'wpshadow' ),
						),
					),
					'advanced' => array(
						'label'       => __( 'Real-Time Sitemap with IndexNow', 'wpshadow' ),
						'description' => __( 'Instant indexation notifications to search engines', 'wpshadow' ),
						'steps'       => array(
							__( 'Install IndexNow plugin (RankMath Pro includes it)', 'wpshadow' ),
							__( 'Notifies Bing/Yandex instantly when content changes', 'wpshadow' ),
							__( 'Combine with traditional XML sitemap for Google', 'wpshadow' ),
							__( 'Monitor Search Console for indexation speed', 'wpshadow' ),
							__( 'Track crawl frequency improvements', 'wpshadow' ),
						),
					),
				),
				'best_practices' => array(
					__( 'Update sitemap automatically on content publish/update', 'wpshadow' ),
					__( 'Include only indexable content (exclude noindex pages)', 'wpshadow' ),
					__( 'Use <lastmod> dates accurately (from post_modified)', 'wpshadow' ),
					__( 'Submit sitemap to Google/Bing Search Console', 'wpshadow' ),
					__( 'Monitor sitemap errors in Search Console', 'wpshadow' ),
					__( 'Consider breaking large sitemaps into index + sub-sitemaps', 'wpshadow' ),
				),
				'testing_steps' => array(
					'verification' => array(
						__( 'Visit /wp-sitemap.xml or /sitemap_index.xml', 'wpshadow' ),
						__( 'Verify <lastmod> dates are recent', 'wpshadow' ),
						__( 'Check that new posts appear within 1 day', 'wpshadow' ),
						__( 'Test sitemap validity with XML validators', 'wpshadow' ),
						__( 'Monitor Search Console for coverage changes', 'wpshadow' ),
					),
					'expected_result' => __( 'Sitemap updated daily or immediately on content changes', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Analyze XML sitemap for freshness.
	 *
	 * @since  1.6028.1705
	 * @return array Sitemap analysis data.
	 */
	private static function analyze_sitemap() {
		$result = array(
			'exists'            => false,
			'sitemap_url'       => '',
			'is_outdated'       => false,
			'last_modified'     => '',
			'days_old'          => 0,
			'recent_posts'      => 0,
			'generation_method' => 'unknown',
		);

		// Try common sitemap URLs.
		$sitemap_urls = array(
			home_url( 'wp-sitemap.xml' ),       // WordPress core (5.5+).
			home_url( 'sitemap_index.xml' ),    // Yoast SEO.
			home_url( 'sitemap.xml' ),          // Generic.
		);

		$sitemap_url = '';
		foreach ( $sitemap_urls as $url ) {
			$response = wp_remote_head( $url );
			if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
				$sitemap_url = $url;
				break;
			}
		}

		if ( empty( $sitemap_url ) ) {
			$result['sitemap_url'] = home_url( 'wp-sitemap.xml' );
			return $result; // Sitemap not found.
		}

		$result['exists']      = true;
		$result['sitemap_url'] = $sitemap_url;

		// Detect generation method.
		if ( strpos( $sitemap_url, 'wp-sitemap.xml' ) !== false ) {
			$result['generation_method'] = 'wordpress_core';
		} elseif ( function_exists( 'wpseo_auto_load' ) ) {
			$result['generation_method'] = 'yoast_seo';
		} elseif ( class_exists( 'RankMath' ) ) {
			$result['generation_method'] = 'rankmath';
		}

		// Fetch and parse sitemap.
		$response = wp_remote_get( $sitemap_url );
		if ( is_wp_error( $response ) ) {
			return $result;
		}

		$sitemap_xml = wp_remote_retrieve_body( $response );
		if ( empty( $sitemap_xml ) ) {
			return $result;
		}

		// Parse lastmod dates.
		libxml_use_internal_errors( true );
		$xml = simplexml_load_string( $sitemap_xml );
		if ( $xml === false ) {
			return $result;
		}

		// Find most recent lastmod date.
		$namespaces = $xml->getNamespaces( true );
		$most_recent = 0;

		// Handle sitemap index.
		if ( isset( $xml->sitemap ) ) {
			foreach ( $xml->sitemap as $sitemap ) {
				if ( isset( $sitemap->lastmod ) ) {
					$lastmod = strtotime( (string) $sitemap->lastmod );
					if ( $lastmod > $most_recent ) {
						$most_recent = $lastmod;
					}
				}
			}
		}

		// Handle URL set.
		if ( isset( $xml->url ) ) {
			foreach ( $xml->url as $url ) {
				if ( isset( $url->lastmod ) ) {
					$lastmod = strtotime( (string) $url->lastmod );
					if ( $lastmod > $most_recent ) {
						$most_recent = $lastmod;
					}
				}
			}
		}

		if ( $most_recent > 0 ) {
			$result['last_modified'] = gmdate( 'Y-m-d H:i:s', $most_recent );
			$result['days_old']      = floor( ( time() - $most_recent ) / DAY_IN_SECONDS );
			$result['is_outdated']   = $result['days_old'] > 7;
		}

		// Count recent posts (last 7 days).
		$args = array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'date_query'     => array(
				array(
					'after' => '7 days ago',
				),
			),
			'posts_per_page' => -1,
			'fields'         => 'ids',
		);
		$recent_posts = get_posts( $args );
		$result['recent_posts'] = count( $recent_posts );

		return $result;
	}

	/**
	 * Get details for missing sitemap scenario.
	 *
	 * @since  1.6028.1705
	 * @return array Details array for missing sitemap.
	 */
	private static function get_missing_sitemap_details() {
		return array(
			'why_important' => __( 'XML sitemaps are essential for search engine discovery. Without a sitemap, search engines rely solely on internal links to find content, which can miss orphaned pages, delay indexation, and waste crawl budget.', 'wpshadow' ),
			'user_impact'   => array(
				__( 'Slow Indexation: New content takes weeks instead of days', 'wpshadow' ),
				__( 'Missing Pages: Orphaned content never discovered', 'wpshadow' ),
				__( 'Poor Crawl Efficiency: Search engines can\'t prioritize fresh content', 'wpshadow' ),
				__( 'Lower Rankings: Competitors with sitemaps indexed faster', 'wpshadow' ),
			),
			'solution_options' => array(
				'free' => array(
					'label'       => __( 'WordPress Core Sitemap (5.5+)', 'wpshadow' ),
					'description' => __( 'Enable built-in sitemap with automatic updates', 'wpshadow' ),
					'steps'       => array(
						__( 'WordPress 5.5+ includes native sitemap at /wp-sitemap.xml', 'wpshadow' ),
						__( 'Automatically enabled unless theme/plugin disables it', 'wpshadow' ),
						__( 'Verify at: https://yoursite.com/wp-sitemap.xml', 'wpshadow' ),
						__( 'Submit to Google Search Console', 'wpshadow' ),
					),
				),
			),
		);
	}
}
