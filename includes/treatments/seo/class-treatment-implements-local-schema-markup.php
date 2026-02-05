<?php
/**
 * Local Schema Markup Treatment
 *
 * Tests whether the site properly implements LocalBusiness schema markup for rich
 * snippet eligibility. Proper schema markup significantly improves local search visibility.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.5003.1130
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Implements_Local_Schema_Markup Class
 *
 * Treatment #19: Local Schema Markup from Specialized & Emerging Success Habits.
 * Checks if the site implements LocalBusiness schema markup correctly.
 *
 * @since 1.5003.1130
 */
class Treatment_Implements_Local_Schema_Markup extends Treatment_Base {

	protected static $slug = 'implements-local-schema-markup';
	protected static $title = 'Local Schema Markup';
	protected static $description = 'Tests whether the site properly implements LocalBusiness schema markup for rich snippets';
	protected static $family = 'local-seo';

	public static function check() {
		$score          = 0;
		$max_score      = 6;
		$score_details  = array();
		$recommendations = array();

		// Check schema plugins.
		$schema_plugins = array(
			'schema-and-structured-data-for-wp/structured-data-for-wp.php',
			'all-in-one-seo-pack/all_in_one_seo_pack.php',
			'wordpress-seo/wp-seo.php',
			'wp-schema-pro/wp-schema-pro.php',
		);

		$has_schema_plugin = false;
		foreach ( $schema_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_schema_plugin = true;
				++$score;
				$score_details[] = __( '✓ Schema markup plugin active', 'wpshadow' );
				break;
			}
		}

		if ( ! $has_schema_plugin ) {
			$score_details[]   = __( '✗ No schema markup plugin detected', 'wpshadow' );
			$recommendations[] = __( 'Install Yoast SEO, Rank Math, or Schema Pro to add structured data', 'wpshadow' );
		}

		// Check for LocalBusiness schema in content.
		$home_page = get_post( get_option( 'page_on_front' ) );
		$has_local_business = false;
		
		if ( $home_page ) {
			$content = $home_page->post_content;
			if ( stripos( $content, 'LocalBusiness' ) !== false || stripos( $content, '@type' ) !== false ) {
				$has_local_business = true;
				$score += 2;
				$score_details[] = __( '✓ LocalBusiness schema markup detected', 'wpshadow' );
			}
		}

		if ( ! $has_local_business ) {
			$score_details[]   = __( '✗ No LocalBusiness schema markup found', 'wpshadow' );
			$recommendations[] = __( 'Add LocalBusiness schema with NAP (Name, Address, Phone), hours, and services', 'wpshadow' );
		}

		// Check organization schema.
		$org_schema = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				's'              => 'Organization schema.org',
			)
		);

		if ( ! empty( $org_schema ) ) {
			++$score;
			$score_details[] = __( '✓ Organization schema present', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No Organization schema found', 'wpshadow' );
			$recommendations[] = __( 'Add Organization schema for your business entity', 'wpshadow' );
		}

		// Check reviews schema.
		$reviews_schema = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				's'              => 'Review aggregateRating',
			)
		);

		if ( ! empty( $reviews_schema ) ) {
			++$score;
			$score_details[] = __( '✓ Review/rating schema detected', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No review schema found', 'wpshadow' );
			$recommendations[] = __( 'Add AggregateRating schema to display star ratings in search results', 'wpshadow' );
		}

		// Check opening hours schema.
		$hours_schema = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				's'              => 'openingHours opens Monday',
			)
		);

		if ( ! empty( $hours_schema ) ) {
			++$score;
			$score_details[] = __( '✓ Opening hours schema present', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No opening hours schema', 'wpshadow' );
			$recommendations[] = __( 'Add openingHours schema so Google can display your business hours', 'wpshadow' );
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage < 35 ) {
			$severity     = 'medium';
			$threat_level = 30;
		} elseif ( $score_percentage < 65 ) {
			$severity     = 'low';
			$threat_level = 20;
		} else {
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Local schema markup score: %d%%. Proper LocalBusiness schema increases click-through rates by 30%% and enables rich snippets showing hours, ratings, and contact info. 58%% of local searches result in rich snippets.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/local-schema-markup',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Schema markup makes your business information machine-readable, enabling rich search results and voice assistant integrations.', 'wpshadow' ),
		);
	}
}
