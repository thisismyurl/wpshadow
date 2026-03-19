<?php
/**
 * Custom Field Mapping Failures Diagnostic
 *
 * Detects when custom fields fail to import correctly.
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
 * Custom Field Mapping Failures Diagnostic Class
 *
 * Detects when custom fields (post meta) fail to import or map incorrectly.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Custom_Field_Mapping_Failures extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'custom-field-mapping-failures';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Custom Field Mapping Failures';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects when custom fields fail to import correctly';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for ACF plugin.
		$has_acf = class_exists( 'ACF' );
		$has_cmb2 = class_exists( 'CMB2' );

		// Sample posts with meta.
		$posts_with_meta = get_posts( array(
			'post_type'      => 'any',
			'posts_per_page' => 10,
			'orderby'        => 'modified',
			'order'          => 'DESC',
		) );

		if ( empty( $posts_with_meta ) ) {
			return null;
		}

		// Check for common custom field patterns.
		$missing_fields = 0;

		if ( $has_acf ) {
			// Check for ACF field groups.
			foreach ( $posts_with_meta as $post ) {
				$acf_data = get_post_meta( $post->ID, '_custom_meta', true );
				if ( empty( $acf_data ) ) {
					$missing_fields++;
				}
			}

			if ( $missing_fields === count( $posts_with_meta ) ) {
				$issues[] = __( 'ACF custom fields not found on any posts', 'wpshadow' );
			}
		}

		if ( $has_cmb2 ) {
			// Check for CMB2 fields.
			$posts_with_cmb2 = 0;
			foreach ( $posts_with_meta as $post ) {
				$cmb2_data = get_post_meta( $post->ID, '_cmb2_data', true );
				if ( ! empty( $cmb2_data ) ) {
					$posts_with_cmb2++;
				}
			}

			if ( $posts_with_cmb2 === 0 && $has_cmb2 ) {
				$issues[] = __( 'CMB2 custom fields not found on any posts', 'wpshadow' );
			}
		}

		// Check for serialization errors in meta.
		global $wpdb;
		$bad_serialized = $wpdb->get_results( "
			SELECT COUNT(*) as count
			FROM {$wpdb->postmeta}
			WHERE meta_value LIKE 'a:%'
			AND meta_value NOT LIKE 's:%'
			LIMIT 5
		" );

		if ( ! empty( $bad_serialized ) && $bad_serialized[0]->count > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of potentially corrupted serialized fields */
				__( '%d potentially corrupted serialized meta fields detected', 'wpshadow' ),
				$bad_serialized[0]->count
			);
		}

		// Check for unserialize errors.
		$meta_values = $wpdb->get_results( "
			SELECT meta_value
			FROM {$wpdb->postmeta}
			WHERE meta_value LIKE 'O:%'
			LIMIT 5
		" );

		$unserialize_errors = 0;
		foreach ( $meta_values as $row ) {
			$unserialized = @unserialize( $row->meta_value );
			if ( $unserialized === false ) {
				$unserialize_errors++;
			}
		}

		if ( $unserialize_errors > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of meta values that cannot be unserialized */
				__( '%d meta values cannot be unserialized (potential corruption)', 'wpshadow' ),
				$unserialize_errors
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/custom-field-mapping-failures',
			);
		}

		return null;
	}
}
