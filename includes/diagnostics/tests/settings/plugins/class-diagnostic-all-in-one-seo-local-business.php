<?php
/**
 * All In One Seo Local Business Diagnostic
 *
 * All In One Seo Local Business configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.702.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All In One Seo Local Business Diagnostic Class
 *
 * @since 1.702.0000
 */
class Diagnostic_AllInOneSeoLocalBusiness extends Diagnostic_Base {

	protected static $slug = 'all-in-one-seo-local-business';
	protected static $title = 'All In One Seo Local Business';
	protected static $description = 'All In One Seo Local Business configuration issues';
	protected static $family = 'functionality';

	public static function check() {
		// Check if AIOSEO is installed
		if ( ! function_exists( 'aioseo' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		// Check if local business is enabled
		$local_business = aioseo()->options->searchAppearance->global->schema->siteRepresentation ?? '';
		if ( $local_business !== 'organization' && $local_business !== 'person' ) {
			$issues[] = 'site_representation_not_set';
			$threat_level += 15;
		}

		// Check organization details
		$org_name = aioseo()->options->searchAppearance->global->schema->organizationName ?? '';
		$org_logo = aioseo()->options->searchAppearance->global->schema->organizationLogo ?? '';
		if ( empty( $org_name ) || empty( $org_logo ) ) {
			$issues[] = 'incomplete_organization_info';
			$threat_level += 15;
		}

		// Check local business info
		$phone = aioseo()->options->searchAppearance->global->schema->phone ?? '';
		$address = aioseo()->options->searchAppearance->global->schema->address ?? array();
		if ( empty( $phone ) || empty( $address['streetAddress'] ?? '' ) ) {
			$issues[] = 'incomplete_contact_info';
			$threat_level += 10;
		}

		// Check opening hours
		$opening_hours = aioseo()->options->searchAppearance->global->schema->openingHours ?? array();
		if ( empty( $opening_hours ) ) {
			$issues[] = 'no_opening_hours';
			$threat_level += 10;
		}

		// Check multiple locations
		$locations = aioseo()->options->searchAppearance->global->schema->locations ?? array();
		if ( ! empty( $locations ) && count( $locations ) > 10 ) {
			$issues[] = 'too_many_locations';
			$threat_level += 5;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of local business issues */
				__( 'All in One SEO local business has incomplete data: %s. This prevents rich local search results and reduces visibility in Google Maps.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/all-in-one-seo-local-business',
			);
		}
		
		return null;
	}
}
