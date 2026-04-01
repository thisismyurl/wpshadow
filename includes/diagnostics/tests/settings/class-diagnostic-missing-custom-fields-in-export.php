<?php
/**
 * Missing Custom Fields in Export Diagnostic
 *
 * Tests whether post meta (custom fields) are included in
 * WordPress export files.
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
 * Missing Custom Fields in Export Diagnostic Class
 *
 * Tests for custom field (post meta) inclusion in WordPress exports.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Missing_Custom_Fields_In_Export extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-custom-fields-in-export';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Custom Fields in Export';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether custom fields are included in exports';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'export';

	/**
	 * Run the diagnostic check.
	 *
	 * Verifies that post meta (custom fields) are included
	 * in WordPress export files.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		global $wpdb;

		// Check for posts with custom meta data.
		$posts_with_meta = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT post_id)
				FROM {$wpdb->postmeta}
				WHERE post_id IN (
					SELECT ID FROM {$wpdb->posts}
					WHERE post_status = %s
					AND post_type IN (%s, %s)
				)
				AND meta_key NOT LIKE %s",
				'publish',
				'post',
				'page',
				'%_edit_lock%'
			)
		);

		// Count total custom meta entries.
		$total_meta_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->postmeta}
				WHERE post_id IN (
					SELECT ID FROM {$wpdb->posts}
					WHERE post_status = %s
					AND post_type IN (%s, %s)
				)
				AND meta_key NOT LIKE %s
				AND meta_key NOT LIKE %s",
				'publish',
				'post',
				'page',
				'%_edit_lock%',
				'%_edit_last%'
			)
		);

		// Check for framework-specific custom fields.
		$acf_fields = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT post_id)
				FROM {$wpdb->postmeta}
				WHERE meta_key LIKE %s
				AND post_id IN (
					SELECT ID FROM {$wpdb->posts}
					WHERE post_status = %s
				)",
				'_acf_%',
				'publish'
			)
		);

		$cmb2_fields = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT post_id)
				FROM {$wpdb->postmeta}
				WHERE meta_key LIKE %s
				AND post_id IN (
					SELECT ID FROM {$wpdb->posts}
					WHERE post_status = %s
				)",
				'_cmb2_%',
				'publish'
			)
		);

		$pods_fields = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT post_id)
				FROM {$wpdb->postmeta}
				WHERE meta_key LIKE %s
				AND post_id IN (
					SELECT ID FROM {$wpdb->posts}
					WHERE post_status = %s
				)",
				'_pods_%',
				'publish'
			)
		);

		// Get sample meta keys.
		$meta_keys = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT meta_key
				FROM {$wpdb->postmeta}
				WHERE post_id IN (
					SELECT ID FROM {$wpdb->posts}
					WHERE post_status = %s
					AND post_type IN (%s, %s)
				)
				AND meta_key NOT LIKE %s
				LIMIT 20",
				'publish',
				'post',
				'page',
				'%_edit%'
			)
		);

		// Check for serialized data in custom fields.
		$serialized_meta = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->postmeta}
				WHERE post_id IN (
					SELECT ID FROM {$wpdb->posts}
					WHERE post_status = %s
				)
				AND (meta_value LIKE %s OR meta_value LIKE %s)",
				'publish',
				'a:%',
				'O:%'
			)
		);

		// Check WXR export meta support.
		$wxr_skip_postmeta = has_filter( 'wxr_export_skip_postmeta' );
		$wxr_meta_excluded = apply_filters( 'wxr_export_skip_postmeta', false );

		// Check for export-related plugins.
		$export_plugins = array(
			'export-post-types/export-post-types.php' => 'Export Custom Post Types',
			'all-in-one-import-export/wp-import-export.php' => 'All In One Import Export',
		);

		$export_plugin_active = false;
		foreach ( $export_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$export_plugin_active = true;
				break;
			}
		}

		if ( $total_meta_count > 0 && ( $posts_with_meta > 0 || $acf_fields > 0 || $cmb2_fields > 0 ) ) {
			$custom_field_frameworks = array();

			if ( $acf_fields > 0 ) {
				$custom_field_frameworks[] = 'ACF (Advanced Custom Fields)';
			}
			if ( $cmb2_fields > 0 ) {
				$custom_field_frameworks[] = 'CMB2';
			}
			if ( $pods_fields > 0 ) {
				$custom_field_frameworks[] = 'Pods';
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of custom fields, %d: number of posts */
					__( '%d custom field entries across %d posts are not included in standard exports', 'wpshadow' ),
					$total_meta_count,
					$posts_with_meta
				),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/missing-custom-fields-in-export?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'posts_with_custom_fields'        => $posts_with_meta,
					'total_custom_field_entries'      => $total_meta_count,
					'acf_fields_detected'             => $acf_fields,
					'cmb2_fields_detected'            => $cmb2_fields,
					'pods_fields_detected'            => $pods_fields,
					'custom_field_frameworks_used'    => $custom_field_frameworks,
					'serialized_data_entries'         => $serialized_meta,
					'sample_custom_meta_keys'         => array_column( $meta_keys, 'meta_key' ),
					'wxr_meta_export_filter_active'   => (bool) $wxr_skip_postmeta,
					'meta_excluded_by_filter'         => $wxr_meta_excluded,
					'export_plugin_available'         => $export_plugin_active,
					'functionality_risk'              => sprintf(
						/* translators: %d: number of posts */
						__( '%d posts will lose advanced functionality without custom fields', 'wpshadow' ),
						$posts_with_meta
					),
					'data_loss_severity'              => __( 'Complete data loss of custom field values in backup', 'wpshadow' ),
					'restore_incompleteness'          => __( 'Restored site will be missing critical functionality and data', 'wpshadow' ),
					'fix_methods'                     => array(
						__( 'Use export plugin with custom field support', 'wpshadow' ),
						__( 'Remove wxr_export_skip_postmeta filter to enable meta export', 'wpshadow' ),
						__( 'Export with admin plugin that preserves postmeta', 'wpshadow' ),
						__( 'Create custom export tool that includes all postmeta', 'wpshadow' ),
						__( 'Use database backup instead of XML export for complete data', 'wpshadow' ),
					),
					'verification'                    => array(
						__( 'Download WXR export and inspect XML', 'wpshadow' ),
						__( 'Search for <wp:postmeta> entries', 'wpshadow' ),
						__( 'Count meta entries vs actual count', 'wpshadow' ),
						__( 'Test import on staging site', 'wpshadow' ),
						__( 'Verify custom field values after import', 'wpshadow' ),
					),
					'critical_note'                   => __( 'Custom fields are often invisible but critical - backups must include all postmeta', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
