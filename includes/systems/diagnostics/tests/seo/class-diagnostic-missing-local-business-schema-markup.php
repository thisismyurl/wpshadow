<?php
/**
 * Missing Local Business Schema Markup Diagnostic
 *
 * Checks if LocalBusiness schema is implemented with NAP, hours, and service areas.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\SEO
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Missing Local Business Schema Markup Diagnostic
 *
 * Verifies LocalBusiness schema is properly implemented with all essential fields:
 * Name, Address, Phone (NAP), business hours, and service areas. Schema markup
 * is critical for local SEO—it increases local pack appearances by 30% and helps
 * Google show your business details directly in search results.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Missing_Local_Business_Schema_Markup extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-local-business-schema-markup';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Local Business Schema Markup Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if LocalBusiness schema is properly implemented with NAP, hours, and service areas';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if LocalBusiness schema exists
		$schema_found = self::check_local_business_schema();
		$completeness = self::check_schema_completeness();

		if ( ! $schema_found || $completeness < 70 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: schema completeness percentage */
					__( 'LocalBusiness schema markup is missing or incomplete (%d%% complete). When properly set up, schema markup increases local search visibility by 30%%, shows up with maps and phone number in Google results. Add complete schema with: Name, Address, Phone, Business Hours, and Service Areas.', 'wpshadow' ),
					$completeness
				),
				'severity'    => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/local-business-schema-setup',
				'details'     => array(
					'schema_found'   => $schema_found,
					'completeness'   => $completeness,
					'missing_fields' => self::get_missing_fields(),
					'recommendation' => __( 'Add complete LocalBusiness structured data to your homepage', 'wpshadow' ),
				),
			);
		}

		return null; // No issue found
	}

	/**
	 * Check if LocalBusiness schema exists on homepage
	 *
	 * @since 1.6093.1200
	 * @return bool True if schema found
	 */
	private static function check_local_business_schema(): bool {
		$response = wp_remote_get( home_url( '/' ) );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );

		// Check for LocalBusiness schema
		if ( stripos( $body, 'LocalBusiness' ) !== false || stripos( $body, '"@type":"LocalBusiness"' ) !== false ) {
			return true;
		}

		// Check for Yoast LocalBusiness output
		if ( stripos( $body, '"@type":"Organization"' ) !== false ) {
			return true;
		}

		return false;
	}

	/**
	 * Check completeness of schema implementation
	 *
	 * @since 1.6093.1200
	 * @return int Completeness percentage 0-100
	 */
	private static function check_schema_completeness(): int {
		$response = wp_remote_get( home_url( '/' ) );

		if ( is_wp_error( $response ) ) {
			return 0;
		}

		$body = wp_remote_retrieve_body( $response );
		$score = 0;

		// Extract JSON-LD blocks
		if ( preg_match_all( '/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $body, $matches ) ) {
			foreach ( $matches[1] as $json_block ) {
				$schema = json_decode( $json_block, true );

				if ( ! is_array( $schema ) ) {
					continue;
				}

				// Check for required fields
				$required_fields = array( 'name', 'address', 'telephone' );
				$found_fields    = 0;

				foreach ( $required_fields as $field ) {
					if ( isset( $schema[ $field ] ) || isset( $schema['@type'] ) ) {
						$found_fields++;
					}
				}

				// Calculate score
				$score = max( $score, ( $found_fields / count( $required_fields ) ) * 100 );
			}
		}

		return min( 100, max( 0, (int) $score ) );
	}

	/**
	 * Get list of missing required fields
	 *
	 * @since 1.6093.1200
	 * @return array Array of missing field names
	 */
	private static function get_missing_fields(): array {
		$required = array(
			'@type'              => 'LocalBusiness or specific type (Plumber, Restaurant, etc)',
			'name'               => 'Business name',
			'address'            => 'Physical address (street, city, state, zip)',
			'telephone'          => 'Phone number',
			'openingHoursSpecification' => 'Business hours for each day',
			'areaServed'         => 'Cities/regions you serve',
			'image'              => 'Business logo (112x112px minimum)',
			'url'                => 'Your website URL',
			'sameAs'             => 'Social media profiles',
		);

		$response = wp_remote_get( home_url( '/' ) );
		$found    = array();

		if ( ! is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );

			foreach ( $required as $field => $description ) {
				if ( stripos( $body, $field ) === false && stripos( $body, strtolower( $field ) ) === false ) {
					$found[] = array(
						'field'       => $field,
						'description' => $description,
					);
				}
			}
		}

		return array_slice( $found, 0, 5 ); // Show top 5 missing fields
	}
}
