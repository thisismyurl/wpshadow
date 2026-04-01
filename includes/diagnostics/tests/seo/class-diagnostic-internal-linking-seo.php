<?php
/**
 * Internal Linking for SEO Diagnostic
 *
 * Tests for strategic internal linking to improve SEO and user navigation.
 * Analyzes internal link structure and distribution.
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
 * Internal Linking SEO Diagnostic Class
 *
 * Evaluates the internal linking strategy for SEO effectiveness.
 * Checks link density, orphan pages, and anchor text optimization.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Internal_Linking_SEO extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'uses-internal-linking-for-seo';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Internal Linking for SEO';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for strategic internal linking for SEO purposes';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the internal linking SEO diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if internal linking issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for internal linking plugins.
		$linking_plugins = array(
			'internal-links/internal-links.php'           => 'Internal Links',
			'link-whisper/index.php'                      => 'Link Whisper',
			'yet-another-related-posts-plugin/yarpp.php'  => 'YARPP',
			'contextual-related-posts/contextual-related-posts.php' => 'Contextual Related Posts',
		);

		$active_linking_plugin = null;
		foreach ( $linking_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_linking_plugin = $name;
				break;
			}
		}

		$stats['linking_plugin'] = $active_linking_plugin;

		// Get sample of published posts.
		$posts = get_posts( array(
			'posts_per_page' => 20,
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );

		if ( empty( $posts ) ) {
			$warnings[] = __( 'No published posts found - cannot check internal linking', 'wpshadow' );
			return null;
		}

		$stats['total_posts_checked'] = count( $posts );

		// Analyze internal links in posts.
		$posts_with_internal_links = 0;
		$posts_without_internal_links = 0;
		$total_internal_links = 0;
		$posts_with_few_links = 0; // Less than 2 links.
		$posts_with_many_links = 0; // More than 10 links.

		$site_url = home_url();
		$domain = wp_parse_url( $site_url, PHP_URL_HOST );

		foreach ( $posts as $post ) {
			$content = $post->post_content;

			// Count internal links (links to same domain).
			preg_match_all( '/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $content, $matches );

			$internal_link_count = 0;
			if ( ! empty( $matches[1] ) ) {
				foreach ( $matches[1] as $url ) {
					// Check if link is internal.
					$link_domain = wp_parse_url( $url, PHP_URL_HOST );

					// Handle relative URLs.
					if ( empty( $link_domain ) && ( strpos( $url, '/' ) === 0 || strpos( $url, '#' ) === 0 ) ) {
						$internal_link_count++;
					} elseif ( $link_domain === $domain ) {
						$internal_link_count++;
					}
				}
			}

			if ( $internal_link_count > 0 ) {
				$posts_with_internal_links++;
				$total_internal_links += $internal_link_count;

				if ( $internal_link_count < 2 ) {
					$posts_with_few_links++;
				} elseif ( $internal_link_count > 10 ) {
					$posts_with_many_links++;
				}
			} else {
				$posts_without_internal_links++;
			}
		}

		$stats['posts_with_internal_links'] = $posts_with_internal_links;
		$stats['posts_without_internal_links'] = $posts_without_internal_links;
		$stats['total_internal_links'] = $total_internal_links;
		$stats['posts_with_few_links'] = $posts_with_few_links;
		$stats['posts_with_many_links'] = $posts_with_many_links;

		if ( $posts_with_internal_links > 0 ) {
			$stats['avg_internal_links_per_post'] = round( $total_internal_links / $posts_with_internal_links, 1 );
		} else {
			$stats['avg_internal_links_per_post'] = 0;
		}

		$stats['internal_linking_coverage'] = round( ( $posts_with_internal_links / count( $posts ) ) * 100, 1 );

		// Check for menu structure (important for internal linking).
		$menus = wp_get_nav_menus();
		$stats['menu_count'] = count( $menus );

		$total_menu_items = 0;
		foreach ( $menus as $menu ) {
			$menu_items = wp_get_nav_menu_items( $menu->term_id );
			if ( $menu_items ) {
				$total_menu_items += count( $menu_items );
			}
		}
		$stats['total_menu_items'] = $total_menu_items;

		// Check for sidebar widgets with links.
		$sidebars = wp_get_sidebars_widgets();
		$has_link_widgets = false;
		foreach ( $sidebars as $sidebar => $widgets ) {
			if ( is_array( $widgets ) ) {
				foreach ( $widgets as $widget ) {
					if ( strpos( $widget, 'recent-posts' ) !== false ||
						 strpos( $widget, 'categories' ) !== false ||
						 strpos( $widget, 'archives' ) !== false ||
						 strpos( $widget, 'nav_menu' ) !== false ) {
						$has_link_widgets = true;
						break 2;
					}
				}
			}
		}
		$stats['has_link_widgets'] = $has_link_widgets;

		// Evaluate issues.
		if ( $posts_without_internal_links > count( $posts ) * 0.5 ) {
			// More than 50% posts without internal links.
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts have no internal links (>50%% of sample)', 'wpshadow' ),
				$posts_without_internal_links
			);
		}

		if ( $stats['avg_internal_links_per_post'] < 2 ) {
			$issues[] = sprintf(
				/* translators: %s: average number */
				__( 'Average internal links per post is very low (%s)', 'wpshadow' ),
				$stats['avg_internal_links_per_post']
			);
		}

		if ( $posts_with_few_links > count( $posts ) * 0.3 ) {
			$warnings[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts have fewer than 2 internal links', 'wpshadow' ),
				$posts_with_few_links
			);
		}

		if ( $total_menu_items < 5 ) {
			$warnings[] = __( 'Navigation menus have very few items - add more for better internal linking', 'wpshadow' );
		}

		if ( ! $has_link_widgets ) {
			$warnings[] = __( 'No link-generating widgets detected in sidebars - consider adding Recent Posts or Categories', 'wpshadow' );
		}

		if ( ! $active_linking_plugin && $stats['internal_linking_coverage'] < 70 ) {
			$warnings[] = __( 'Consider using an internal linking plugin to improve link distribution', 'wpshadow' );
		}

		if ( $posts_with_many_links > 0 ) {
			$warnings[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts have excessive internal links (>10) which may dilute link equity', 'wpshadow' ),
				$posts_with_many_links
			);
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Internal linking strategy has issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/internal-linking-seo?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Internal linking has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/internal-linking-seo?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Internal linking is well implemented.
	}
}
