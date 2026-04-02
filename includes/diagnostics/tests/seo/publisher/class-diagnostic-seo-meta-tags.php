<?php
/**
 * SEO Meta Tags Diagnostic
 *
 * Checks if articles have proper meta descriptions and SEO tags.
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
 * SEO Meta Tags Diagnostic Class
 *
 * Verifies that articles and pages have proper meta descriptions,
 * titles, and other SEO-critical tags.
 *
 * @since 1.6093.1200
 */
class Diagnostic_SEO_Meta_Tags extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'seo-meta-tags';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SEO Meta Tags';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if articles have proper meta descriptions and SEO tags';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'publisher';

	/**
	 * Run the SEO meta tags diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if SEO issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for SEO plugins.
		$seo_plugins = array(
			'wordpress-seo/wp-seo.php'                   => 'Yoast SEO',
			'all-in-one-seo-pack/all_in_one_seo_pack.php' => 'All in One SEO',
			'rank-math-seo/rank-math-seo.php'            => 'Rank Math',
			'the-seo-framework/the-seo-framework.php'    => 'The SEO Framework',
		);

		$active_seo_plugin = null;
		foreach ( $seo_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_seo_plugin = $name;
				break;
			}
		}

		$stats['seo_plugin'] = $active_seo_plugin;

		// Get sample of published posts.
		$posts = get_posts( array(
			'posts_per_page' => 10,
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );

		if ( empty( $posts ) ) {
			$warnings[] = __( 'No published posts found - cannot check SEO tags', 'wpshadow' );
			return null;
		}

		$posts_with_meta_description = 0;
		$posts_without_meta_description = 0;
		$short_meta_descriptions = 0;
		$long_meta_descriptions = 0;
		$posts_missing_title = 0;

		foreach ( $posts as $post ) {
			// Check meta description.
			$meta_description = get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true );
			
			// Try alternate SEO plugin meta fields.
			if ( empty( $meta_description ) ) {
				$meta_description = get_post_meta( $post->ID, '_aioseo_description', true );
			}
			if ( empty( $meta_description ) ) {
				$meta_description = get_post_meta( $post->ID, 'rank_math_description', true );
			}

			if ( ! empty( $meta_description ) ) {
				$posts_with_meta_description++;

				// Check length (optimal: 150-160 characters).
				$meta_length = strlen( $meta_description );
				if ( $meta_length < 120 ) {
					$short_meta_descriptions++;
				} elseif ( $meta_length > 160 ) {
					$long_meta_descriptions++;
				}
			} else {
				$posts_without_meta_description++;
			}

			// Check title.
			$post_title = get_post_meta( $post->ID, '_yoast_wpseo_title', true );
			if ( empty( $post_title ) ) {
				$post_title = get_the_title( $post->ID );
			}

			if ( empty( $post_title ) ) {
				$posts_missing_title++;
			}
		}

		$stats['total_sample_posts'] = count( $posts );
		$stats['posts_with_meta_description'] = $posts_with_meta_description;
		$stats['posts_without_meta_description'] = $posts_without_meta_description;
		$stats['short_meta_descriptions'] = $short_meta_descriptions;
		$stats['long_meta_descriptions'] = $long_meta_descriptions;

		// Calculate percentage.
		$meta_coverage_percent = ( $posts_with_meta_description / count( $posts ) ) * 100;
		$stats['meta_description_coverage'] = round( $meta_coverage_percent, 1 );

		// Check for issues.
		if ( $posts_without_meta_description > count( $posts ) * 0.3 ) {
			// More than 30% missing meta descriptions.
			$issues[] = number_format_i18n( (int) $posts_without_meta_description ) . ' ' . __( 'posts missing meta descriptions (>30% of sample)', 'wpshadow' );
		}

		if ( $posts_missing_title > 0 ) {
			$issues[] = number_format_i18n( (int) $posts_missing_title ) . ' ' . __( 'posts missing SEO titles', 'wpshadow' );
		}

		// Check for recommendations.
		if ( ! $active_seo_plugin ) {
			$warnings[] = __( 'No SEO plugin active - consider installing one to manage meta tags', 'wpshadow' );
		}

		if ( $short_meta_descriptions > 0 ) {
			$warnings[] = number_format_i18n( (int) $short_meta_descriptions ) . ' ' . __( 'posts have short meta descriptions (<120 chars)', 'wpshadow' );
		}

		if ( $long_meta_descriptions > 0 ) {
			$warnings[] = number_format_i18n( (int) $long_meta_descriptions ) . ' ' . __( 'posts have long meta descriptions (>160 chars)', 'wpshadow' );
		}

		// Check for social meta tags.
		$has_opengraph = false;
		$has_twitter_card = false;

		// Check in active theme.
		$theme = wp_get_theme();
		$theme_dir = $theme->get_stylesheet_directory();
		$header_file = $theme_dir . '/header.php';

		if ( file_exists( $header_file ) ) {
			$header_content = file_get_contents( $header_file );
			
			if ( strpos( $header_content, 'og:' ) !== false || 
				 function_exists( 'the_open_graph_protocol' ) ) {
				$has_opengraph = true;
			}

			if ( strpos( $header_content, 'twitter:' ) !== false ) {
				$has_twitter_card = true;
			}
		}

		$stats['opengraph_tags'] = $has_opengraph;
		$stats['twitter_cards'] = $has_twitter_card;

		if ( ! $has_opengraph ) {
			$warnings[] = __( 'No Open Graph tags detected - add for better social sharing', 'wpshadow' );
		}

		if ( ! $has_twitter_card ) {
			$warnings[] = __( 'No Twitter Card tags detected - add for better Twitter sharing', 'wpshadow' );
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'SEO meta tags have critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/seo-meta-tags',
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
				'description'  => __( 'SEO meta tags have recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/seo-meta-tags',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // SEO meta tags are good.
	}
}
