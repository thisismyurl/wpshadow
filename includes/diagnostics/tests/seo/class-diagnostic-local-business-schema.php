<?php
/**
 * Local Business Schema Markup Diagnostic
 *
 * Issue #4803: Missing Local Business Schema Markup
 * Family: business-performance
 *
 * Checks if local business has Schema.org LocalBusiness markup.
 * Local schema helps Google show business info in search results.
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
 * Diagnostic_Local_Business_Schema Class
 *
 * Checks for LocalBusiness schema markup.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Local_Business_Schema extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'local-business-schema';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Local Business Schema Markup';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if Schema.org LocalBusiness markup is present for local businesses';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		$issues[] = __( 'Add LocalBusiness schema with name, address, phone (NAP)', 'wpshadow' );
		$issues[] = __( 'Include business hours: openingHoursSpecification', 'wpshadow' );
		$issues[] = __( 'Add geo coordinates: latitude/longitude for map results', 'wpshadow' );
		$issues[] = __( 'Specify business type: Restaurant, Hotel, Store, etc.', 'wpshadow' );
		$issues[] = __( 'Include review aggregate rating if you have reviews', 'wpshadow' );
		$issues[] = __( 'Use Schema plugin (Rank Math, Yoast Local SEO) or manual JSON-LD', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your local business might be missing Schema.org LocalBusiness markup, making it harder for Google to show your business info in search results. What is LocalBusiness schema? Structured data (code) that explicitly tells Google: Your business name, address, phone (NAP), Business hours, Geo coordinates (for maps), Business category (restaurant, hotel, store, etc.), Reviews/ratings. Why it matters: 1) Google Knowledge Panel: Your business card on right side of search results, 2) Google Maps: Accurate location and info, 3) Rich results: Hours, ratings, contact info directly in search, 4) Voice search: "OK Google, is [Business] open now?" (needs hours schema), 5) Local pack: Top 3 local results with map. Required fields: @type: "LocalBusiness" (or specific type like "Restaurant"), name: Business name, address: Street, city, state, zip, telephone: Phone number, openingHoursSpecification: Days and hours. Recommended fields: geo: Latitude/longitude coordinates, url: Website URL, priceRange: $, $$, $$$, $$$$, image: Logo or photo, aggregateRating: Average star rating. How to implement: Rank Math: Built-in schema editor (free), Yoast Local SEO: Premium plugin for local businesses, Schema Pro: Drag-and-drop schema builder, Manual: Add JSON-LD code to footer template. Test: Google Rich Results Test (search.google.com/test/rich-results) to validate schema. Example business types: Restaurant, Dentist, Hotel, Store, Plumber, RealEstateAgent.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/local-business-schema?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'       => $issues,
					'required_fields'       => 'name, address, telephone, openingHours',
					'seo_benefit'           => 'Knowledge Panel, Maps, Rich Results, Voice Search',
					'tools'                 => 'Rank Math (free), Yoast Local SEO, Schema Pro',
					'testing'               => 'Google Rich Results Test',
					'business_types'        => 'Restaurant, Hotel, Store, Plumber, Dentist, etc.',
					'voice_search'          => 'Enables "Is [Business] open now?" queries',
				),
			);
		}

		return null;
	}
}
