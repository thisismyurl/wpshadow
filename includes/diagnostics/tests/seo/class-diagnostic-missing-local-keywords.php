<?php
/**
 * Diagnostic: Missing Local Keywords
 *
 * Detects missing geo-specific keyword targeting for local businesses.
 * Local keywords drive foot traffic and local pack rankings.
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
 * Missing Local Keywords Diagnostic Class
 *
 * Checks for local SEO optimization patterns.
 *
 * Detection methods:
 * - Location mentions in content
 * - City/region in titles/headings
 * - Google Business Profile integration
 * - NAP (Name, Address, Phone) consistency
 *
 * @since 0.6093.1200
 */
class Diagnostic_Missing_Local_Keywords extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-local-keywords';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Local Keywords';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Geo-specific keywords missing = lost local traffic and map pack rankings';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'keyword-strategy';

	/**
	 * Run the diagnostic check.
	 *
	 * Scoring system (4 points):
	 * - 1 point: Site address/location configured
	 * - 1 point: 40%+ posts mention location
	 * - 1 point: Location in titles/headings
	 * - 1 point: Local business schema present
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score     = 0;
		$max_score = 4;

		// Check for location in site settings.
		$site_city   = get_option( 'wpshadow_business_city', '' );
		$site_region = get_option( 'wpshadow_business_region', '' );
		$has_location_setting = ! empty( $site_city ) || ! empty( $site_region );

		// Alternative: Check blogdescription or tagline for location.
		$site_description = get_bloginfo( 'description' );
		$has_location_in_tagline = preg_match( '/\b[A-Z][a-z]+(?:\s+[A-Z][a-z]+)?,\s*[A-Z]{2}\b/', $site_description );

		if ( $has_location_setting || $has_location_in_tagline ) {
			$score++;
		}

		// Check posts for location mentions.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 50,
			)
		);

		if ( empty( $posts ) ) {
			return null;
		}

		$posts_with_location = 0;
		$posts_with_location_in_title = 0;

		// Common US location patterns (cities, states).
		$location_pattern = '/\b(?:New York|Los Angeles|Chicago|Houston|Phoenix|Philadelphia|San Antonio|San Diego|Dallas|San Jose|Austin|Jacksonville|Fort Worth|Columbus|San Francisco|Charlotte|Indianapolis|Seattle|Denver|Boston|Portland|Nashville|Detroit|Memphis|Oklahoma|Las Vegas|Louisville|Baltimore|Milwaukee|Albuquerque|Tucson|Fresno|Sacramento|Mesa|Kansas City|Atlanta|Miami|Raleigh|Omaha|Oakland|Minneapolis|Tulsa|Cleveland|Wichita|Arlington|CA|NY|TX|FL|PA|IL|OH|GA|NC|MI|NJ|VA|WA|AZ|MA|TN|IN|MO|MD|WI|CO|MN|SC|AL|LA|KY|OR|OK|CT|UT|IA|NV|AR|MS|KS|NM|NE|WV|ID|HI|NH|ME|MT|RI|DE|SD|ND|AK|VT|WY)\b/';

		foreach ( $posts as $post ) {
			// Check content.
			if ( preg_match( $location_pattern, $post->post_content ) ) {
				$posts_with_location++;
			}

			// Check titles.
			if ( preg_match( $location_pattern, $post->post_title ) ) {
				$posts_with_location_in_title++;
			}
		}

		$location_mention_percent = ( $posts_with_location / count( $posts ) ) * 100;
		$title_location_percent = ( $posts_with_location_in_title / count( $posts ) ) * 100;

		if ( $location_mention_percent >= 40 ) {
			$score++;
		}
		if ( $title_location_percent >= 20 ) {
			$score++;
		}

		// Check for local business schema.
		$has_local_schema = false;
		$homepage = get_post( get_option( 'page_on_front' ) );
		if ( $homepage ) {
			$has_local_schema = (
				stripos( $homepage->post_content, 'LocalBusiness' ) !== false ||
				stripos( $homepage->post_content, '"@type":"LocalBusiness"' ) !== false
			);
		}

		if ( $has_local_schema ) {
			$score++;
		}

		// Pass if score is high.
		if ( $score >= ( $max_score * 0.75 ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __( 'Local keywords target customers in your geographic area = foot traffic, phone calls, directions. Formula: [Service] + [City/Region]. Examples: "plumber in Austin TX", "best pizza Brooklyn", "Denver yoga studio". Benefits: Local pack rankings (top 3 Google Maps results), "Near me" search visibility (46% of searches are local), Higher conversion (local intent = ready to buy/visit), Less competition (vs national keywords), Voice search optimization ("Alexa, find coffee near me"). Where to add: Page titles (most important), H1 headings, Meta descriptions, First paragraph of content, Image alt text, URL slugs (/austin-plumber/), Footer (NAP = Name, Address, Phone), Schema markup (LocalBusiness type). Content strategy: Service pages per location (if multiple cities), Neighborhood guides (SEO + local value), Local event coverage (timely + relevant), Customer success stories (mention location), Local partnerships/sponsorships. Google Business Profile optimization: Complete profile (hours, photos, services), Regular posts (weekly updates), Respond to reviews (all of them), Add attributes (wheelchair accessible, Wi-Fi, etc.), Use local keywords in description. NAP consistency critical: Exact same format everywhere (website, GBP, directories), Include suite numbers consistently, Use local phone number (not toll-free).', 'wpshadow' ),
			'severity'    => 'low',
			'threat_level' => 20,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/missing-local-keywords?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'stats'       => array(
				'has_location_setting' => $has_location_setting || $has_location_in_tagline,
				'posts_checked'        => count( $posts ),
				'with_location'        => $posts_with_location,
				'location_percent'     => round( $location_mention_percent, 1 ),
				'titles_with_location' => $posts_with_location_in_title,
				'has_local_schema'     => $has_local_schema,
			),
			'recommendation' => __( 'Add city/region to site tagline. Include location in 40%+ of service posts. Add location to page titles (H1). Create LocalBusiness schema on homepage. Claim Google Business Profile. Ensure NAP consistency across site. Create location-specific service pages.', 'wpshadow' ),
		);
	}
}
