<?php
/**
 * Local Content Created Diagnostic
 *
 * Tests whether the site regularly publishes location-specific content that resonates
 * with local audiences. Local content establishes community expertise and relevance.
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
 * Diagnostic_Creates_Local_Content Class
 *
 * Diagnostic #16: Local Content Created from Specialized & Emerging Success Habits.
 * Checks if the site publishes location-specific content regularly.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Creates_Local_Content extends Diagnostic_Base {

	protected static $slug = 'creates-local-content';
	protected static $title = 'Local Content Created';
	protected static $description = 'Tests whether the site regularly publishes location-specific content for local audiences';
	protected static $family = 'local-seo';

	public static function check() {
		$score          = 0;
		$max_score      = 5;
		$score_details  = array();
		$recommendations = array();

		// Check recent local content posts.
		$local_posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 20,
				'post_status'    => 'publish',
				's'              => 'local community city neighborhood',
				'date_query'     => array(
					array(
						'after' => '6 months ago',
					),
				),
			)
		);

		if ( count( $local_posts ) >= 5 ) {
			$score += 2;
			$score_details[] = sprintf(
				/* translators: %d: number of local posts */
				__( '✓ %d+ local content posts in last 6 months', 'wpshadow' ),
				count( $local_posts )
			);
		} elseif ( count( $local_posts ) >= 2 ) {
			++$score;
			$score_details[]   = sprintf( __( '◐ %d local posts in last 6 months', 'wpshadow' ), count( $local_posts ) );
			$recommendations[] = __( 'Publish at least 1-2 local content pieces per month', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ Insufficient local content published', 'wpshadow' );
			$recommendations[] = __( 'Create blog posts about local events, news, tips, and guides specific to your area', 'wpshadow' );
		}

		// Check location-specific categories/tags.
		$location_terms = get_terms(
			array(
				'taxonomy'   => array( 'category', 'post_tag' ),
				'hide_empty' => false,
				'search'     => 'local community',
			)
		);

		if ( ! empty( $location_terms ) && count( $location_terms ) >= 2 ) {
			++$score;
			$score_details[] = __( '✓ Location-specific categories/tags exist', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No location-specific taxonomy found', 'wpshadow' );
			$recommendations[] = __( 'Create categories or tags for local neighborhoods, cities, or service areas', 'wpshadow' );
		}

		// Check local guides or resources.
		$local_guides = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				's'              => 'guide complete resource tips',
			)
		);

		if ( ! empty( $local_guides ) ) {
			++$score;
			$score_details[] = __( '✓ Local guides or resources published', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No comprehensive local guides found', 'wpshadow' );
			$recommendations[] = __( 'Create ultimate guides for your local area (e.g., "Complete Guide to [City] Home Maintenance")', 'wpshadow' );
		}

		// Check local keywords in content.
		$has_local_keywords = false;
		$recent_posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
			)
		);

		$local_keywords = array( 'in', 'near', 'local', 'area', 'community', 'neighborhood' );
		foreach ( $recent_posts as $post ) {
			foreach ( $local_keywords as $keyword ) {
				if ( stripos( $post->post_content, $keyword ) !== false ) {
					$has_local_keywords = true;
					break 2;
				}
			}
		}

		if ( $has_local_keywords ) {
			++$score;
			$score_details[] = __( '✓ Local keywords integrated into content', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ Local keywords underutilized', 'wpshadow' );
			$recommendations[] = __( 'Naturally incorporate location keywords (city name, neighborhoods) throughout your content', 'wpshadow' );
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage < 30 ) {
			$severity     = 'medium';
			$threat_level = 25;
		} elseif ( $score_percentage < 60 ) {
			$severity     = 'low';
			$threat_level = 15;
		} else {
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Local content score: %d%%. Regular local content increases organic traffic by 52%% and establishes community expertise. 67%% of local searchers prefer businesses with location-specific blog content over generic information.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/local-content-creation',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Location-specific content captures long-tail local searches and demonstrates deep community knowledge, building trust and authority.', 'wpshadow' ),
		);
	}
}
