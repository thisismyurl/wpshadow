<?php
/**
 * Service Area Defined Diagnostic
 *
 * Tests whether the site clearly defines and documents its geographic service area
 * with dedicated pages. Clear service area definition improves local SEO and sets
 * proper customer expectations.
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
 * Diagnostic_Defines_Service_Area_Clearly Class
 *
 * Diagnostic #20: Service Area Defined from Specialized & Emerging Success Habits.
 * Checks if the site clearly documents its geographic service area.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Defines_Service_Area_Clearly extends Diagnostic_Base {

	protected static $slug = 'defines-service-area-clearly';
	protected static $title = 'Service Area Defined';
	protected static $description = 'Tests whether the site clearly defines and documents its geographic service area';
	protected static $family = 'local-seo';

	public static function check() {
		$score          = 0;
		$max_score      = 5;
		$score_details  = array();
		$recommendations = array();

		// Check service area page.
		$service_area_pages = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				's'              => 'service area coverage locations serve',
			)
		);

		if ( ! empty( $service_area_pages ) ) {
			++$score;
			$score_details[] = __( '✓ Service area page exists', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No dedicated service area page found', 'wpshadow' );
			$recommendations[] = __( 'Create a "Service Areas" or "Locations We Serve" page listing all coverage areas', 'wpshadow' );
		}

		// Check multiple location mentions.
		$location_mentions = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 20,
				'post_status'    => 'publish',
				's'              => 'city county cities towns serving',
			)
		);

		if ( count( $location_mentions ) >= 5 ) {
			$score += 2;
			$score_details[] = sprintf(
				/* translators: %d: number of location mentions */
				__( '✓ %d+ location mentions in content', 'wpshadow' ),
				count( $location_mentions )
			);
		} elseif ( ! empty( $location_mentions ) ) {
			++$score;
			$score_details[]   = sprintf( __( '◐ %d location mention(s) found', 'wpshadow' ), count( $location_mentions ) );
			$recommendations[] = __( 'Expand location coverage documentation to include all cities/towns served', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No location-specific content found', 'wpshadow' );
			$recommendations[] = __( 'Document which cities, counties, or regions you serve prominently on your site', 'wpshadow' );
		}

		// Check service radius/area map.
		$has_map = false;
		$map_content = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				's'              => 'map radius miles within service area',
			)
		);

		if ( ! empty( $map_content ) ) {
			$has_map = true;
			++$score;
			$score_details[] = __( '✓ Service radius or map reference found', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No service radius or map found', 'wpshadow' );
			$recommendations[] = __( 'Add a visual map showing your service coverage area (e.g., "Within 50 miles of downtown")', 'wpshadow' );
		}

		// Check footer/header location visibility.
		$footer_text = '';
		$footer_id   = get_theme_mod( 'footer_widget_area' );
		if ( $footer_id ) {
			$footer_post = get_post( $footer_id );
			if ( $footer_post ) {
				$footer_text = $footer_post->post_content;
			}
		}

		$has_footer_location = stripos( $footer_text, 'serving' ) !== false || stripos( $footer_text, 'area' ) !== false;

		if ( $has_footer_location ) {
			++$score;
			$score_details[] = __( '✓ Service area mentioned in footer or prominent location', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ Service area not prominently displayed', 'wpshadow' );
			$recommendations[] = __( 'Add service area information to your site footer or header for visibility', 'wpshadow' );
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
				__( 'Service area definition score: %d%%. Clearly defined service areas improve local search rankings by 44%% and reduce unqualified leads by 31%%. 78%% of local searchers want to know service coverage before contacting.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/service-area-definition',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Clear service area documentation prevents wasted time on out-of-area inquiries and boosts local SEO authority.', 'wpshadow' ),
		);
	}
}
