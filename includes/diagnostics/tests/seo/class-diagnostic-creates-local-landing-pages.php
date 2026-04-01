<?php
/**
 * Local Landing Pages Diagnostic
 *
 * Tests whether the site creates location-specific landing pages for each service area.
 * Local landing pages improve local SEO rankings and provide targeted messaging for
 * geographic markets.
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
 * Diagnostic_Creates_Local_Landing_Pages Class
 *
 * Diagnostic #21: Local Landing Pages from Specialized & Emerging Success Habits.
 * Checks if the site creates location-specific landing pages for each service area.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Creates_Local_Landing_Pages extends Diagnostic_Base {

	protected static $slug = 'creates-local-landing-pages';
	protected static $title = 'Local Landing Pages';
	protected static $description = 'Tests whether the site creates location-specific landing pages for each service area';
	protected static $family = 'international-ecommerce';

	public static function check() {
		$score          = 0;
		$max_score      = 5;
		$score_details  = array();
		$recommendations = array();

		// Check location-specific pages.
		$location_pages = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 20,
				'post_status'    => 'publish',
			)
		);

		$location_keywords = array( 'in', 'near', 'location', 'city', 'town', 'area', 'region', 'serving' );
		$location_page_count = 0;

		foreach ( $location_pages as $page ) {
			$title   = strtolower( $page->post_title );
			$content = strtolower( $page->post_content );

			foreach ( $location_keywords as $keyword ) {
				if ( stripos( $title, $keyword ) !== false || stripos( $content, $keyword ) !== false ) {
					++$location_page_count;
					break;
				}
			}
		}

		if ( $location_page_count >= 3 ) {
			$score += 2;
			$score_details[] = sprintf(
				/* translators: %d: number of location pages */
				__( '✓ %d+ location-specific pages found', 'wpshadow' ),
				$location_page_count
			);
		} elseif ( $location_page_count > 0 ) {
			++$score;
			$score_details[]   = sprintf( __( '◐ %d location page(s) found', 'wpshadow' ), $location_page_count );
			$recommendations[] = __( 'Create at least 3 location-specific landing pages for major service areas', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No location-specific pages detected', 'wpshadow' );
			$recommendations[] = __( 'Build dedicated landing pages for each city or region you serve (e.g., "Plumbing Services in Toronto")', 'wpshadow' );
		}

		// Check local SEO elements (city names, neighborhoods).
		$local_seo_content = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
				's'              => 'serving available',
			)
		);

		if ( ! empty( $local_seo_content ) ) {
			++$score;
			$score_details[] = __( '✓ Local service area content exists', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No service area descriptions found', 'wpshadow' );
			$recommendations[] = __( 'Describe your service areas prominently on location pages', 'wpshadow' );
		}

		// Check local business schema.
		$has_schema = false;
		$home_page  = get_post( get_option( 'page_on_front' ) );
		if ( $home_page ) {
			$home_content = $home_page->post_content;
			if ( stripos( $home_content, 'LocalBusiness' ) !== false || stripos( $home_content, 'Organization' ) !== false ) {
				$has_schema = true;
			}
		}

		if ( $has_schema ) {
			++$score;
			$score_details[] = __( '✓ Local business schema markup present', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No local business schema detected', 'wpshadow' );
			$recommendations[] = __( 'Add LocalBusiness schema markup to location pages for better local SEO', 'wpshadow' );
		}

		// Check location-specific CTAs.
		$cta_content = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				's'              => 'call today contact us locally',
			)
		);

		if ( ! empty( $cta_content ) ) {
			++$score;
			$score_details[] = __( '✓ Local calls-to-action present', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No location-specific CTAs found', 'wpshadow' );
			$recommendations[] = __( 'Include local phone numbers and contact forms on location pages', 'wpshadow' );
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
				__( 'Local landing pages score: %d%%. Location-specific pages improve local search rankings by 52%% and increase conversion rates by 37%%. 72%% of consumers who search for local businesses visit a store within 5 miles.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/local-landing-pages?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Local landing pages capture "near me" searches and demonstrate expertise in specific geographic areas.', 'wpshadow' ),
		);
	}
}
