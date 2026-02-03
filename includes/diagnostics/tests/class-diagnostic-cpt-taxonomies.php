<?php
/**
 * Custom Post Type Taxonomies Diagnostic
 *
 * Verifies that all WPShadow custom taxonomies are properly registered
 * and associated with their respective post types.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6034.1230
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_CPT_Taxonomies Class
 *
 * Checks if all WPShadow custom taxonomies are registered and linked.
 *
 * @since 1.6034.1230
 */
class Diagnostic_CPT_Taxonomies extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cpt-taxonomies';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Custom Post Type Taxonomies';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies all custom taxonomies are properly registered and linked';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Expected taxonomies mapped to post types
	 *
	 * @var array
	 */
	private static $expected_taxonomies = array(
		'testimonial_category' => array( 'testimonial' ),
		'testimonial_tag'      => array( 'testimonial' ),
		'team_department'      => array( 'team_member' ),
		'team_location'        => array( 'team_member' ),
		'portfolio_category'   => array( 'portfolio_item' ),
		'portfolio_tag'        => array( 'portfolio_item' ),
		'event_category'       => array( 'wps_event' ),
		'event_tag'            => array( 'wps_event' ),
		'resource_category'    => array( 'resource' ),
		'resource_type'        => array( 'resource' ),
		'case_study_category'  => array( 'case_study' ),
		'case_study_tag'       => array( 'case_study' ),
		'service_category'     => array( 'service' ),
		'service_tag'          => array( 'service' ),
		'location_type'        => array( 'location' ),
		'doc_category'         => array( 'documentation' ),
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Verifies that all expected taxonomies are registered and linked to post types.
	 *
	 * @since  1.6034.1230
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$missing_taxonomies = array();
		$unlinked_taxonomies = array();

		foreach ( self::$expected_taxonomies as $taxonomy_slug => $post_types ) {
			// Check if taxonomy is registered.
			if ( ! taxonomy_exists( $taxonomy_slug ) ) {
				$missing_taxonomies[] = $taxonomy_slug;
				continue;
			}

			// Check if taxonomy is linked to expected post types.
			$taxonomy_object = get_taxonomy( $taxonomy_slug );
			if ( $taxonomy_object ) {
				foreach ( $post_types as $post_type ) {
					if ( ! in_array( $post_type, (array) $taxonomy_object->object_type, true ) ) {
						$unlinked_taxonomies[] = sprintf(
							/* translators: 1: taxonomy slug, 2: post type slug */
							__( '%1$s (not linked to %2$s)', 'wpshadow' ),
							$taxonomy_slug,
							$post_type
						);
					}
				}
			}
		}

		// If any taxonomies are missing or unlinked, report finding.
		if ( ! empty( $missing_taxonomies ) || ! empty( $unlinked_taxonomies ) ) {
			$description = '';

			if ( ! empty( $missing_taxonomies ) ) {
				$description .= sprintf(
					/* translators: %s: comma-separated list of taxonomy slugs */
					__( 'The following taxonomies are not registered: %s. ', 'wpshadow' ),
					implode( ', ', $missing_taxonomies )
				);
			}

			if ( ! empty( $unlinked_taxonomies ) ) {
				$description .= sprintf(
					/* translators: %s: comma-separated list of taxonomy issues */
					__( 'The following taxonomies have linking issues: %s. ', 'wpshadow' ),
					implode( ', ', $unlinked_taxonomies )
				);
			}

			$description .= __( 'This may prevent proper content categorization and filtering.', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/custom-taxonomies-setup',
				'academy_link' => 'https://wpshadow.com/academy/organizing-content-with-taxonomies',
			);
		}

		return null; // All taxonomies are properly registered and linked.
	}

	/**
	 * Get total count of registered taxonomies
	 *
	 * @since  1.6034.1230
	 * @return int Count of registered taxonomies.
	 */
	public static function get_registered_count() {
		$count = 0;
		foreach ( array_keys( self::$expected_taxonomies ) as $taxonomy_slug ) {
			if ( taxonomy_exists( $taxonomy_slug ) ) {
				++$count;
			}
		}
		return $count;
	}
}
