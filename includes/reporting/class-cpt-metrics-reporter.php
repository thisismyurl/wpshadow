<?php
/**
 * CPT Metrics Reporter
 *
 * Collects and reports metrics about custom post types usage,
 * content health, and feature adoption.
 *
 * @package    WPShadow
 * @subpackage Reporting
 * @since      1.6034.1230
 */

declare(strict_types=1);

namespace WPShadow\Reporting;

use WPShadow\Diagnostics\Diagnostic_CPT_Registration;
use WPShadow\Diagnostics\Diagnostic_CPT_Taxonomies;
use WPShadow\Diagnostics\Diagnostic_CPT_Block_Patterns;
use WPShadow\Diagnostics\Diagnostic_CPT_Content_Health;
use WPShadow\Diagnostics\Diagnostic_CPT_Features;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT_Metrics_Reporter Class
 *
 * Provides metrics and insights about CPT usage.
 *
 * @since 1.6034.1230
 */
class CPT_Metrics_Reporter {

	/**
	 * Get comprehensive CPT metrics
	 *
	 * @since  1.6034.1230
	 * @return array {
	 *     CPT metrics array.
	 *
	 *     @type array  $post_types       Post type statistics.
	 *     @type array  $taxonomies       Taxonomy statistics.
	 *     @type array  $features         Feature adoption stats.
	 *     @type int    $content_health   Health score (0-100).
	 *     @type int    $block_patterns   Pattern count.
	 *     @type array  $recommendations  Actionable recommendations.
	 * }
	 */
	public static function get_metrics() {
		return array(
			'post_types'      => self::get_post_type_stats(),
			'taxonomies'      => self::get_taxonomy_stats(),
			'features'        => self::get_feature_stats(),
			'content_health'  => self::get_content_health_score(),
			'block_patterns'  => self::get_block_pattern_count(),
			'recommendations' => self::get_recommendations(),
		);
	}

	/**
	 * Get post type statistics
	 *
	 * @since  1.6034.1230
	 * @return array Post type stats.
	 */
	private static function get_post_type_stats() {
		$cpt_slugs = array(
			'testimonial',
			'team_member',
			'portfolio_item',
			'wps_event',
			'resource',
			'case_study',
			'service',
			'location',
			'documentation',
			'wps_product',
		);

		$stats = array(
			'registered_count' => 0,
			'total_posts'      => 0,
			'published_posts'  => 0,
			'draft_posts'      => 0,
			'most_used'        => null,
			'least_used'       => null,
			'by_type'          => array(),
		);

		$post_counts = array();

		foreach ( $cpt_slugs as $cpt_slug ) {
			if ( ! post_type_exists( $cpt_slug ) ) {
				continue;
			}

			++$stats['registered_count'];

			$counts = wp_count_posts( $cpt_slug );
			$published = isset( $counts->publish ) ? (int) $counts->publish : 0;
			$draft = isset( $counts->draft ) ? (int) $counts->draft : 0;

			$stats['total_posts'] += $published + $draft;
			$stats['published_posts'] += $published;
			$stats['draft_posts'] += $draft;

			$post_counts[ $cpt_slug ] = $published;

			$stats['by_type'][ $cpt_slug ] = array(
				'published' => $published,
				'draft'     => $draft,
				'total'     => $published + $draft,
			);
		}

		// Determine most and least used.
		if ( ! empty( $post_counts ) ) {
			arsort( $post_counts );
			$stats['most_used'] = key( $post_counts );

			$post_counts_filtered = array_filter( $post_counts );
			if ( ! empty( $post_counts_filtered ) ) {
				$stats['least_used'] = array_key_last( $post_counts_filtered );
			}
		}

		return $stats;
	}

	/**
	 * Get taxonomy statistics
	 *
	 * @since  1.6034.1230
	 * @return array Taxonomy stats.
	 */
	private static function get_taxonomy_stats() {
		if ( class_exists( 'WPShadow\Diagnostics\Diagnostic_CPT_Taxonomies' ) ) {
			$registered_count = Diagnostic_CPT_Taxonomies::get_registered_count();
		} else {
			$registered_count = 0;
		}

		return array(
			'registered_count' => $registered_count,
			'expected_count'   => 15, // From diagnostic class.
		);
	}

	/**
	 * Get feature adoption statistics
	 *
	 * @since  1.6034.1230
	 * @return array Feature stats.
	 */
	private static function get_feature_stats() {
		$stats = array(
			'active_features'   => 0,
			'expected_features' => 0,
			'adoption_rate'     => 0,
			'cloud_enabled'     => false,
		);

		if ( class_exists( 'WPShadow\Diagnostics\Diagnostic_CPT_Features' ) ) {
			$stats['active_features'] = Diagnostic_CPT_Features::get_active_count();
			$stats['expected_features'] = Diagnostic_CPT_Features::get_expected_count();

			if ( $stats['expected_features'] > 0 ) {
				$stats['adoption_rate'] = ( $stats['active_features'] / $stats['expected_features'] ) * 100;
			}
		}

		$stats['cloud_enabled'] = ! empty( get_option( 'wpshadow_cloud_api_key' ) );

		return $stats;
	}

	/**
	 * Get content health score
	 *
	 * @since  1.6034.1230
	 * @return int Health score (0-100).
	 */
	private static function get_content_health_score() {
		if ( class_exists( 'WPShadow\Diagnostics\Diagnostic_CPT_Content_Health' ) ) {
			return Diagnostic_CPT_Content_Health::get_health_score();
		}

		return 100; // Default to perfect if diagnostic not available.
	}

	/**
	 * Get block pattern count
	 *
	 * @since  1.6034.1230
	 * @return int Pattern count.
	 */
	private static function get_block_pattern_count() {
		if ( class_exists( 'WPShadow\Diagnostics\Diagnostic_CPT_Block_Patterns' ) ) {
			return Diagnostic_CPT_Block_Patterns::get_registered_count();
		}

		return 0;
	}

	/**
	 * Get actionable recommendations
	 *
	 * @since  1.6034.1230
	 * @return array Array of recommendation strings.
	 */
	private static function get_recommendations() {
		$recommendations = array();
		$metrics = self::get_metrics();

		// Check CPT registration.
		if ( class_exists( 'WPShadow\Diagnostics\Diagnostic_CPT_Registration' ) ) {
			$expected = Diagnostic_CPT_Registration::get_expected_count();
			$registered = Diagnostic_CPT_Registration::get_registered_count();

			if ( $registered < $expected ) {
				$recommendations[] = array(
					'type'         => 'warning',
					'title'        => __( 'Missing Custom Post Types', 'wpshadow' ),
					'description'  => sprintf(
						/* translators: 1: registered count, 2: expected count */
						__( 'Only %1$d of %2$d custom post types are registered. Ensure all CPT files are present and initialized.', 'wpshadow' ),
						$registered,
						$expected
					),
					'action'       => array(
						'text' => __( 'Learn More', 'wpshadow' ),
						'url'  => 'https://wpshadow.com/kb/custom-post-types-setup',
					),
					'academy_link' => 'https://wpshadow.com/academy/understanding-custom-post-types',
				);
			}
		}

		// Check content health.
		$health_score = $metrics['content_health'];
		if ( $health_score < 70 ) {
			$recommendations[] = array(
				'type'         => 'warning',
				'title'        => __( 'Content Quality Issues', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %d: health score percentage */
					__( 'Your content health score is %d%%. Many posts are missing featured images, excerpts, or have short content. Improving content quality can enhance user engagement and SEO.', 'wpshadow' ),
					round( $health_score )
				),
				'action'       => array(
					'text' => __( 'View Content Report', 'wpshadow' ),
					'url'  => admin_url( 'admin.php?page=wpshadow-analytics' ),
				),
				'academy_link' => 'https://wpshadow.com/academy/content-best-practices',
			);
		}

		// Check feature adoption.
		if ( $metrics['features']['adoption_rate'] < 100 ) {
			$missing = $metrics['features']['expected_features'] - $metrics['features']['active_features'];
			$recommendations[] = array(
				'type'         => 'info',
				'title'        => __( 'Unlock More Features', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %d: number of inactive features */
					_n(
						'%d productivity feature is not active. Enable it to enhance your content management workflow.',
						'%d productivity features are not active. Enable them to enhance your content management workflow.',
						$missing,
						'wpshadow'
					),
					$missing
				),
				'action'       => array(
					'text' => __( 'View Features', 'wpshadow' ),
					'url'  => admin_url( 'admin.php?page=wpshadow' ),
				),
				'academy_link' => 'https://wpshadow.com/academy/maximizing-cpt-productivity',
			);
		}

		// Check block patterns.
		if ( $metrics['block_patterns'] === 0 ) {
			$recommendations[] = array(
				'type'         => 'info',
				'title'        => __( 'Block Patterns Available', 'wpshadow' ),
				'description'  => __( 'Pre-designed block patterns are available to speed up content creation. These provide professional layouts you can insert with one click.', 'wpshadow' ),
				'action'       => array(
					'text' => __( 'Learn About Patterns', 'wpshadow' ),
					'url'  => 'https://wpshadow.com/kb/using-block-patterns',
				),
				'academy_link' => 'https://wpshadow.com/academy/block-patterns-quick-start',
			);
		}

		// Check cloud features.
		if ( ! $metrics['features']['cloud_enabled'] ) {
			$recommendations[] = array(
				'type'         => 'info',
				'title'        => __( 'AI Content Suggestions Available', 'wpshadow' ),
				'description'  => __( 'Register for WPShadow Cloud to unlock AI-powered content suggestions that can improve, expand, summarize, and optimize your content for SEO.', 'wpshadow' ),
				'action'       => array(
					'text' => __( 'Learn More', 'wpshadow' ),
					'url'  => 'https://wpshadow.com/cloud',
				),
				'academy_link' => 'https://wpshadow.com/academy/ai-content-creation',
			);
		}

		return $recommendations;
	}

	/**
	 * Get dashboard widget data
	 *
	 * @since  1.6034.1230
	 * @return array Widget data for dashboard display.
	 */
	public static function get_dashboard_widget_data() {
		$metrics = self::get_metrics();

		return array(
			'title'    => __( 'Custom Post Types Overview', 'wpshadow' ),
			'subtitle' => sprintf(
				/* translators: %d: number of registered CPTs */
				_n(
					'%d custom post type registered',
					'%d custom post types registered',
					$metrics['post_types']['registered_count'],
					'wpshadow'
				),
				$metrics['post_types']['registered_count']
			),
			'stats'    => array(
				array(
					'label' => __( 'Total Posts', 'wpshadow' ),
					'value' => number_format_i18n( $metrics['post_types']['total_posts'] ),
					'icon'  => 'dashicons-media-document',
				),
				array(
					'label' => __( 'Published', 'wpshadow' ),
					'value' => number_format_i18n( $metrics['post_types']['published_posts'] ),
					'icon'  => 'dashicons-yes-alt',
				),
				array(
					'label' => __( 'Content Health', 'wpshadow' ),
					'value' => round( $metrics['content_health'] ) . '%',
					'icon'  => 'dashicons-heart',
					'class' => self::get_health_class( $metrics['content_health'] ),
				),
				array(
					'label' => __( 'Block Patterns', 'wpshadow' ),
					'value' => number_format_i18n( $metrics['block_patterns'] ),
					'icon'  => 'dashicons-layout',
				),
			),
			'actions'  => array(
				array(
					'text' => __( 'View Analytics', 'wpshadow' ),
					'url'  => admin_url( 'admin.php?page=wpshadow-analytics' ),
					'icon' => 'dashicons-chart-bar',
				),
				array(
					'text' => __( 'Run Diagnostics', 'wpshadow' ),
					'url'  => admin_url( 'admin.php?page=wpshadow' ),
					'icon' => 'dashicons-admin-tools',
				),
			),
		);
	}

	/**
	 * Get CSS class for health score
	 *
	 * @since  1.6034.1230
	 * @param  int $score Health score (0-100).
	 * @return string CSS class.
	 */
	private static function get_health_class( $score ) {
		if ( $score >= 90 ) {
			return 'wpshadow-health-excellent';
		} elseif ( $score >= 70 ) {
			return 'wpshadow-health-good';
		} elseif ( $score >= 50 ) {
			return 'wpshadow-health-fair';
		} else {
			return 'wpshadow-health-poor';
		}
	}
}
