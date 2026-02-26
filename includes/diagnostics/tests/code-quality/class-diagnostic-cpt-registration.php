<?php
/**
 * Custom Post Types Registration Diagnostic
 *
 * Verifies that all WPShadow custom post types are properly registered
 * and accessible in the WordPress admin.
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
 * Diagnostic_CPT_Registration Class
 *
 * Checks if all WPShadow custom post types are registered and functioning.
 *
 * @since 1.6034.1230
 */
class Diagnostic_CPT_Registration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cpt-registration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Custom Post Types Registration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies all WPShadow custom post types are properly registered';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Expected custom post types
	 *
	 * @var array
	 */
	private static $expected_cpts = array(
		'testimonial'    => 'Testimonials',
		'team_member'    => 'Team Members',
		'portfolio_item' => 'Portfolio Items',
		'wps_event'      => 'Events',
		'resource'       => 'Resources',
		'case_study'     => 'Case Studies',
		'service'        => 'Services',
		'location'       => 'Locations',
		'documentation'  => 'Documentation',
		'wps_product'    => 'Products',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Verifies that all expected custom post types are registered in WordPress.
	 *
	 * @since  1.6034.1230
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$missing_cpts  = array();
		$inactive_cpts = array();

		foreach ( self::$expected_cpts as $cpt_slug => $cpt_name ) {
			// Check if post type is registered.
			if ( ! post_type_exists( $cpt_slug ) ) {
				$missing_cpts[] = $cpt_name;
				continue;
			}

			// Check if post type is publicly accessible.
			$post_type_object = get_post_type_object( $cpt_slug );
			if ( ! $post_type_object || ! $post_type_object->public ) {
				$inactive_cpts[] = $cpt_name;
			}
		}

		// If any CPTs are missing or inactive, report finding.
		if ( ! empty( $missing_cpts ) || ! empty( $inactive_cpts ) ) {
			$description = '';

			if ( ! empty( $missing_cpts ) ) {
				$description .= sprintf(
					/* translators: %s: comma-separated list of post type names */
					__( 'The following custom post types are not registered: %s. ', 'wpshadow' ),
					implode( ', ', $missing_cpts )
				);
			}

			if ( ! empty( $inactive_cpts ) ) {
				$description .= sprintf(
					/* translators: %s: comma-separated list of post type names */
					__( 'The following custom post types are registered but not publicly accessible: %s. ', 'wpshadow' ),
					implode( ', ', $inactive_cpts )
				);
			}

			$description .= __( 'This may affect content management functionality and user experience.', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/custom-post-types-setup',
				'academy_link' => 'https://wpshadow.com/academy/understanding-custom-post-types',
			);
		}

		return null; // All CPTs are properly registered.
	}

	/**
	 * Get total count of registered CPTs
	 *
	 * @since  1.6034.1230
	 * @return int Count of registered CPTs.
	 */
	public static function get_registered_count() {
		$count = 0;
		foreach ( array_keys( self::$expected_cpts ) as $cpt_slug ) {
			if ( post_type_exists( $cpt_slug ) ) {
				++$count;
			}
		}
		return $count;
	}

	/**
	 * Get expected CPT count
	 *
	 * @since  1.6034.1230
	 * @return int Total expected CPTs.
	 */
	public static function get_expected_count() {
		return count( self::$expected_cpts );
	}
}
