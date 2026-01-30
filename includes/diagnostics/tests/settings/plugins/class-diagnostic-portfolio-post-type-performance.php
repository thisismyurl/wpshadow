<?php
/**
 * Portfolio Post Type Performance Diagnostic
 *
 * Portfolio queries slowing site.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.497.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Portfolio Post Type Performance Diagnostic Class
 *
 * @since 1.497.0000
 */
class Diagnostic_PortfolioPostTypePerformance extends Diagnostic_Base {

	protected static $slug = 'portfolio-post-type-performance';
	protected static $title = 'Portfolio Post Type Performance';
	protected static $description = 'Portfolio queries slowing site';
	protected static $family = 'performance';

	public static function check() {
		// Check if portfolio post type exists
		if ( ! post_type_exists( 'portfolio' ) && ! post_type_exists( 'jetpack-portfolio' ) ) {
			return null;
		}

		global $wpdb;
		$issues = array();
		$threat_level = 0;

		$post_type = post_type_exists( 'portfolio' ) ? 'portfolio' : 'jetpack-portfolio';

		// Check post count
		$post_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				 WHERE post_type = %s AND post_status = %s",
				$post_type,
				'publish'
			)
		);
		if ( $post_count > 100 ) {
			$issues[] = 'high_post_count';
			$threat_level += 15;
		}

		// Check for posts without featured images
		$without_thumbnails = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} p
				 LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = %s
				 WHERE p.post_type = %s AND p.post_status = %s AND pm.meta_id IS NULL",
				'_thumbnail_id',
				$post_type,
				'publish'
			)
		);
		if ( $without_thumbnails > 10 ) {
			$issues[] = 'missing_featured_images';
			$threat_level += 10;
		}

		// Check taxonomy query performance
		$tax_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->term_taxonomy} 
			 WHERE taxonomy LIKE 'portfolio%'"
		);
		if ( $tax_count > 100 ) {
			$issues[] = 'excessive_taxonomy_terms';
			$threat_level += 15;
		}

		// Check for pagination
		$posts_per_page = get_option( 'posts_per_page', 10 );
		if ( $posts_per_page > 20 && $post_count > 50 ) {
			$issues[] = 'high_posts_per_page';
			$threat_level += 10;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of performance issues */
				__( 'Portfolio post type has performance issues: %s. With %d portfolio items, this can significantly slow page loads.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) ),
				$post_count
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/portfolio-post-type-performance',
			);
		}
		
		return null;
	}
}
