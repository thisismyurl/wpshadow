<?php
/**
 * Advanced Custom Fields Compatibility Diagnostic
 *
 * Tests ACF field group registration and display. Verifies ACF data integrity
 * and detects common ACF configuration issues.
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
 * Advanced Custom Fields Compatibility Diagnostic Class
 *
 * Checks for ACF compatibility and configuration issues.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Advanced_Custom_Fields_Compatibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'advanced-custom-fields-compatibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Advanced Custom Fields Compatibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests ACF field group registration and data integrity';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if ACF is active.
		if ( ! class_exists( 'ACF' ) && ! function_exists( 'acf' ) ) {
			// No ACF installed, check if there's ACF data orphaned.
			global $wpdb;

			$acf_meta = $wpdb->get_var(
				"SELECT COUNT(*)
				FROM {$wpdb->postmeta}
				WHERE meta_key LIKE '\\_acf%%'
				OR meta_key LIKE 'acf%%'"
			);

			if ( $acf_meta > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of ACF meta entries */
					__( 'ACF plugin not active but %d ACF meta entries exist (orphaned data)', 'wpshadow' ),
					$acf_meta
				);
			}

			// Nothing more to check if ACF not active.
			if ( empty( $issues ) ) {
				return null;
			}
		}

		// ACF is active - run compatibility checks.
		if ( function_exists( 'acf' ) ) {
			// Check ACF version.
			$acf_version = defined( 'ACF_VERSION' ) ? ACF_VERSION : acf()->version;
			
			if ( version_compare( $acf_version, '5.0', '<' ) ) {
				$issues[] = sprintf(
					/* translators: %s: ACF version */
					__( 'ACF version %s is outdated (may cause compatibility issues)', 'wpshadow' ),
					esc_html( $acf_version )
				);
			}

			// Check for field groups.
			$field_groups = acf_get_field_groups();

			if ( empty( $field_groups ) ) {
				$issues[] = __( 'ACF is active but no field groups registered (unused plugin)', 'wpshadow' );
			} else {
				// Check for field groups with no fields.
				$empty_groups = 0;
				foreach ( $field_groups as $group ) {
					$fields = acf_get_fields( $group['key'] );
					if ( empty( $fields ) ) {
						++$empty_groups;
					}
				}

				if ( $empty_groups > 0 ) {
					$issues[] = sprintf(
						/* translators: %d: number of empty field groups */
						__( '%d ACF field groups have no fields (misconfiguration)', 'wpshadow' ),
						$empty_groups
					);
				}

				// Check for field groups with missing location rules.
				$missing_location = 0;
				foreach ( $field_groups as $group ) {
					if ( empty( $group['location'] ) ) {
						++$missing_location;
					}
				}

				if ( $missing_location > 0 ) {
					$issues[] = sprintf(
						/* translators: %d: number of groups without location rules */
						__( '%d ACF field groups have no location rules (won\'t display)', 'wpshadow' ),
						$missing_location
					);
				}

				// Check for duplicate field keys.
				$all_field_keys = array();
				foreach ( $field_groups as $group ) {
					$fields = acf_get_fields( $group['key'] );
					if ( $fields ) {
						foreach ( $fields as $field ) {
							$all_field_keys[] = $field['key'];
						}
					}
				}

				$duplicate_keys = array_diff_assoc( $all_field_keys, array_unique( $all_field_keys ) );
				if ( ! empty( $duplicate_keys ) ) {
					$issues[] = sprintf(
						/* translators: %d: number of duplicate field keys */
						__( '%d duplicate ACF field keys detected (causes data conflicts)', 'wpshadow' ),
						count( $duplicate_keys )
					);
				}
			}

			// Check for ACF data without corresponding field definitions.
			global $wpdb;

			$acf_field_names = $wpdb->get_col(
				"SELECT DISTINCT meta_key
				FROM {$wpdb->postmeta}
				WHERE meta_key NOT LIKE '\\_%%'
				AND meta_key NOT LIKE '%%acf%%'
				LIMIT 500"
			);

			$orphaned_acf_data = 0;
			foreach ( $acf_field_names as $field_name ) {
				// Check if corresponding reference field exists.
				$has_reference = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*)
						FROM {$wpdb->postmeta}
						WHERE meta_key = %s
						LIMIT 1",
						'_' . $field_name
					)
				);

				// If no reference but data exists, it might be orphaned ACF data.
				if ( $has_reference > 0 ) {
					$is_acf_field = $wpdb->get_var(
						$wpdb->prepare(
							"SELECT COUNT(*)
							FROM {$wpdb->postmeta}
							WHERE meta_key = %s
							AND meta_value LIKE 'field_%%'
							LIMIT 1",
							'_' . $field_name
						)
					);

					if ( 0 === $is_acf_field ) {
						++$orphaned_acf_data;
					}
				}
			}

			if ( $orphaned_acf_data > 10 ) {
				$issues[] = sprintf(
					/* translators: %d: number of orphaned fields */
					__( '%d fields have orphaned ACF data (field definitions deleted but data remains)', 'wpshadow' ),
					$orphaned_acf_data
				);
			}

			// Check for ACF JSON sync issues.
			if ( function_exists( 'acf_get_local_json_files' ) ) {
				$json_files = acf_get_local_json_files();
				$json_count = ! empty( $json_files ) ? count( $json_files ) : 0;

				if ( $json_count > 0 && count( $field_groups ) > $json_count ) {
					$issues[] = sprintf(
						/* translators: 1: DB count, 2: JSON count */
						__( 'ACF has %1$d groups in database but only %2$d in JSON (sync issue)', 'wpshadow' ),
						count( $field_groups ),
						$json_count
					);
				}
			}

			// Check for flexible content fields (can cause performance issues).
			$flexible_content_count = 0;
			foreach ( $field_groups as $group ) {
				$fields = acf_get_fields( $group['key'] );
				if ( $fields ) {
					foreach ( $fields as $field ) {
						if ( 'flexible_content' === $field['type'] ) {
							++$flexible_content_count;
						}
					}
				}
			}

			if ( $flexible_content_count > 10 ) {
				$issues[] = sprintf(
					/* translators: %d: number of flexible content fields */
					__( '%d flexible content fields detected (can cause performance issues)', 'wpshadow' ),
					$flexible_content_count
				);
			}
		}

		// Check for ACF PRO features used with free version.
		if ( ! defined( 'ACF_PRO' ) || ! ACF_PRO ) {
			global $wpdb;

			// Check for PRO field types.
			$pro_field_types = array( 'repeater', 'flexible_content', 'gallery', 'clone' );
			$pro_fields_used = array();

			foreach ( $pro_field_types as $type ) {
				$count = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*)
						FROM {$wpdb->posts}
						WHERE post_type = 'acf-field'
						AND post_content LIKE %s",
						'%"type":"' . $type . '"%'
					)
				);

				if ( $count > 0 ) {
					$pro_fields_used[] = $type;
				}
			}

			if ( ! empty( $pro_fields_used ) ) {
				$issues[] = sprintf(
					/* translators: %s: list of PRO field types */
					__( 'ACF PRO field types used with free version (%s)', 'wpshadow' ),
					implode( ', ', $pro_fields_used )
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/advanced-custom-fields-compatibility',
			);
		}

		return null;
	}
}
